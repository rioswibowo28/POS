<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Services\PaymentService;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private OrderRepository $orderRepository,
        private PaymentService $paymentService,
        private MidtransService $midtransService
    ) {}

    public function show($orderId)
    {
        $order = $this->orderRepository->with(['items.product', 'table'])->find($orderId);
        
        if (!$order) {
            return redirect()->route('pos.index')->with('error', 'Order not found');
        }
        
        // Check if Midtrans is configured
        $midtransConfigured = MidtransService::isConfigured();
        $midtransClientKey = \App\Models\Setting::get('midtrans_client_key');

        // Prepare cart items for customer display
        $cartItems = $order->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity
            ];
        })->values()->toArray();

        $displayMode = \App\Models\Setting::get('customer_display_mode', 'local');

        return view('orders.payment', compact('order', 'midtransConfigured', 'midtransClientKey', 'cartItems', 'displayMode'));
    }

    public function process(Request $request, $orderId)
    {
        $validated = $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,qris,midtrans',
            'payments.*.amount' => 'required|numeric|min:0',
            'flag' => 'nullable|boolean',
            'business_date_option' => 'nullable|in:today,yesterday',
        ]);

        try {
            // Check if there's an open shift based on settings
            $useShifts = \App\Models\Setting::get('use_shifts', true) == '1';

            if ($useShifts) {
                $currentShift = \App\Models\Shift::getCurrentShift();
                if (!$currentShift) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No shift is currently open. Please open a shift before processing payments.',
                        'error' => 'No open shift'
                    ], 422);
                }
            }

            $order = $this->orderRepository->find($orderId);

            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Update business_date and regenerate numbers if needed
            if (!empty($validated['business_date_option'])) {
                $targetDate = $validated['business_date_option'] === 'yesterday' ? today()->subDay() : today();
                if (\Carbon\Carbon::parse($order->business_date)->format('Y-m-d') !== $targetDate->format('Y-m-d')) {
                    $order->business_date = $targetDate;
                    $order->order_number = \App\Models\Order::generateOrderNumber($order->flag ?? false, $targetDate);
                    $order->bill_number = \App\Models\Order::generateBillNumber($order->flag ?? false, $targetDate);
                    $order->save();
                }
            }

            // Update flag if provided
            if ($request->has('flag')) {
                $newFlag = (bool) $request->input('flag');
                if ($order->flag != $newFlag) {
                    $order->flag = $newFlag;

                    // Regenerate new numbers since flag changed
                    $order->order_number = \App\Models\Order::generateOrderNumber($newFlag, $order->business_date);
                    $order->bill_number = \App\Models\Order::generateBillNumber($newFlag, $order->business_date);
                    $order->save();
                }
            }
            // Check if Midtrans QRIS is among payments
            $hasMidtrans = false;
            $midtransAmount = 0;
            foreach ($validated['payments'] as $p) {
                if ($p['method'] === 'qris' && MidtransService::isConfigured()) {
                    $hasMidtrans = true;
                    $midtransAmount = $p['amount'];
                    break;
                }
            }

            // If payment method is QRIS and Midtrans is configured, use Midtrans
            if ($hasMidtrans) {
                \Log::info('Processing QRIS payment with Midtrans', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => $midtransAmount
                ]);

                // Generate Midtrans Snap Token
                $orderData = [
                    'order_number' => $order->order_number . '-' . time(), // append time to avoid duplicate order id in midtrans
                    'total' => $midtransAmount > 0 ? $midtransAmount : $order->total,
                    'customer_name' => $order->customer_name ?? 'Customer',
                    'customer_email' => $request->input('customer_email'),
                    'customer_phone' => $request->input('customer_phone'),
                    'items' => $order->items->map(function($item) {
                        return [
                            'product_id' => $item->product_id,
                            'name' => $item->name,
                            'price' => $item->price,
                            'quantity' => $item->quantity,
                        ];
                    })->toArray()
                ];

                try {
                    $snapToken = $this->midtransService->createSnapToken($orderData);
                    
                    \Log::info('Snap token generated successfully', [
                        'order_number' => $order->order_number,
                        'token_length' => strlen($snapToken)
                    ]);

                    return response()->json([
                        'success' => true,
                        'use_midtrans' => true,
                        'snap_token' => $snapToken,
                        'message' => 'Snap token generated'
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to generate Snap token', [
                        'error' => $e->getMessage(),
                        'order_number' => $order->order_number
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to generate payment: ' . $e->getMessage()
                    ], 422);
                }
            }

            // Process payments (Regular)
            $lastPayment = null;
            $orderTotalRemaining = $order->total;
            
            // Delete old incomplete payments if any? Assuming new process
            // sum previous payments if partial?
            $previousPaid = $order->payments()->sum('amount');
            $orderTotalRemaining -= $previousPaid;

            foreach ($validated['payments'] as $paymentInput) {
                $amountPaid = $paymentInput['amount'];
                if ($amountPaid <= 0) continue;
                
                $paymentAmount = min($orderTotalRemaining, $amountPaid);
                $receivedAmount = $amountPaid;
                
                $paymentData = [
                    'order_id' => $orderId,
                    'method' => $paymentInput['method'],
                    'amount' => $paymentAmount,
                    'received_amount' => $receivedAmount,
                ];

                $lastPayment = $this->paymentService->processPayment($paymentData);
                $orderTotalRemaining -= $paymentAmount;
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $lastPayment,
                'redirect' => route('orders.receipt', $orderId)
            ]);
        } catch (\Exception $e) {
            \Log::error('Payment processing failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}