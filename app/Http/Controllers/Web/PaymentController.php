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
            'payment_method' => 'required|in:cash,qris,midtrans',
            'amount_paid' => 'required|numeric|min:0',
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
            
            // If payment method is QRIS and Midtrans is configured, use Midtrans
            if ($validated['payment_method'] === 'qris' && MidtransService::isConfigured()) {
                \Log::info('Processing QRIS payment with Midtrans', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total
                ]);
                
                // Generate Midtrans Snap Token
                $orderData = [
                    'order_number' => $order->order_number,
                    'total' => $order->total,
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
            
            // Regular payment processing
            $paymentData = [
                'order_id' => $orderId,
                'method' => $validated['payment_method'],
                'amount' => $order->total,
                'received_amount' => $validated['amount_paid'],
            ];
            
            $payment = $this->paymentService->processPayment($paymentData);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $payment,
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