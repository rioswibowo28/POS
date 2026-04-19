<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MidtransService;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Services\OrderService;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    public function __construct(
        private MidtransService $midtransService,
        private OrderRepository $orderRepository,
        private PaymentRepository $paymentRepository,
        private OrderService $orderService
    ) {}

    /**
     * Handle Midtrans notification/callback
     */
    public function notification(Request $request)
    {
        try {
            $notification = $this->midtransService->handleNotification();
            
            \Log::info('Midtrans Notification:', $notification);
            
            // Extract original order_number from order_id (remove timestamp)
            // Format: ORD-20260220-0002-1708412345 -> ORD-20260220-0002
            $orderId = $notification['order_id'];
            $originalOrderNumber = preg_replace('/-\d{10}$/', '', $orderId);
            
            // Find order by order_number
            $order = $this->orderRepository->where('order_number', $originalOrderNumber)->first();
            
            if (!$order) {
                \Log::warning('Order not found for Midtrans notification', [
                    'order_id' => $orderId,
                    'original_order_number' => $originalOrderNumber
                ]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Update payment based on transaction status
            $transactionStatus = $notification['transaction_status'];
            $fraudStatus = $notification['fraud_status'];

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    // Payment success
                    $this->updatePaymentStatus($order, $notification, 'completed');
                }
            } elseif ($transactionStatus == 'settlement') {
                // Payment success
                $this->updatePaymentStatus($order, $notification, 'completed');
            } elseif ($transactionStatus == 'pending') {
                // Payment pending
                $this->updatePaymentStatus($order, $notification, 'pending');
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                // Payment failed
                $this->updatePaymentStatus($order, $notification, 'failed');
            }

            return response()->json(['message' => 'Notification handled successfully']);
            
        } catch (\Exception $e) {
            \Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }

    /**
     * Update payment status
     */
    private function updatePaymentStatus($order, $notification, $status)
    {
        // Find existing payment or create new one
        $payment = $this->paymentRepository
            ->where('order_id', $order->id)
            ->where('method', 'midtrans')
            ->first();

        $paymentData = [
            'order_id' => $order->id,
            'method' => 'midtrans',
            'amount' => $notification['gross_amount'],
            'status' => $status === 'completed' ? 'paid' : $status,
            'reference_number' => $notification['transaction_id'],
            'notes' => 'Midtrans ' . ($notification['payment_type'] ?? 'payment'),
        ];

        if ($payment) {
            $this->paymentRepository->update($payment->id, $paymentData);
        } else {
            $this->paymentRepository->create($paymentData);
        }

        // Only release table when payment status is truly success.
        if ($status === 'completed') {
            $order->refresh();
            $totalPaid = $order->payments()->where('status', PaymentStatus::PAID)->sum('amount');

            if ($totalPaid >= $order->total && $order->status !== OrderStatus::COMPLETED) {
                $order->paid_by = $order->paid_by ?? auth()->id();
                $order->save();
                $this->orderService->updateOrderStatus($order->id, OrderStatus::COMPLETED);
            }
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus($orderId)
    {
        try {
            $order = $this->orderRepository->find($orderId);
            
            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $status = $this->midtransService->getTransactionStatus($order->order_number);
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
