<?php

namespace App\Services;

use App\Repositories\PaymentRepository;
use App\Repositories\OrderRepository;
use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;
use App\Models\TempOrder;
use App\Models\TempOrderItem;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    protected $paymentRepository;
    protected $orderRepository;
    protected $orderService;

    public function __construct(
        PaymentRepository $paymentRepository,
        OrderRepository $orderRepository,
        OrderService $orderService
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }

    public function getPaymentsByOrder($orderId)
    {
        return $this->paymentRepository->getByOrder($orderId);
    }

    public function getTodayPayments()
    {
        return $this->paymentRepository->getTodayPayments();
    }

    public function getPaymentByNumber($paymentNumber)
    {
        return $this->paymentRepository->getByPaymentNumber($paymentNumber);
    }

    public function processPayment(array $data)
    {
        return DB::transaction(function () use ($data) {
            try {
                $order = $this->orderRepository->findOrFail($data['order_id']);
                
                // Get current shift
                $currentShift = \App\Models\Shift::getCurrentShift();

                // Update order shift_id if not set yet
                if ($currentShift && !$order->shift_id) {
                    $order->shift_id = $currentShift->id;
                    $order->save();
                }

                // Calculate change
                $changeAmount = 0;
                if (isset($data['received_amount'])) {
                    $changeAmount = $data['received_amount'] - $data['amount'];
                }

                // Create payment
                $payment = $this->paymentRepository->create([
                    'order_id' => $data['order_id'],
                    'shift_id' => $currentShift ? $currentShift->id : null,
                    'method' => $data['method'],
                    'status' => 'paid', // Use 'paid' not 'completed'
                    'amount' => $data['amount'],
                    'received_amount' => $data['received_amount'] ?? $data['amount'],
                    'change_amount' => $changeAmount,
                    'reference_number' => $data['reference_number'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);

                // Reload order with payments to get updated total
                $order->load('payments');
                
                // Check if order is fully paid
                $totalPaid = $order->payments->sum('amount');
                
                \Log::info('Payment processed:', [
                    'order_id' => $order->id,
                    'total_paid' => $totalPaid,
                    'order_total' => $order->total,
                    'fully_paid' => $totalPaid >= $order->total
                ]);
                
                if ($totalPaid >= $order->total) {
                    // Update order status to completed and set paid_by
                    $order->paid_by = auth()->id();
                    $order->save();
                    $this->orderService->updateOrderStatus($order->id, OrderStatus::COMPLETED);

                    // If flag=1, move order to temp_orders table and force delete from orders
                    if ($order->flag) {
                        // Pre-load order relation before deletion (cascade will remove payment from DB too)
                        $payment->load('order');
                        $this->moveToTempOrder($order);
                        return $payment;
                    }
                }

                return $payment->load('order');
            } catch (\Exception $e) {
                \Log::error('Payment processing error:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    public function voidPayment($paymentId)
    {
        return DB::transaction(function () use ($paymentId) {
            $payment = $this->paymentRepository->findOrFail($paymentId);
            
            $payment->status = PaymentStatus::FAILED;
            $payment->save();

            return $payment;
        });
    }

    /**
     * Move a flag=1 order to the temp_orders table after successful payment.
     * Copies the order and its items, then soft-deletes the original order.
     */
    protected function moveToTempOrder($order)
    {
        // Reload order with items and payments
        $order->load(['items', 'payments']);

        // Get primary payment info
        $primaryPayment = $order->payments->first();

        // Create TempOrder with same data + payment info
        $tempOrder = TempOrder::create([
            'order_number' => $order->order_number,
            'bill_number' => $order->bill_number,
            'table_id' => $order->table_id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'type' => $order->getRawOriginal('type'),
            'status' => 'completed',
            'subtotal' => $order->subtotal,
            'tax' => $order->tax,
            'discount' => $order->discount,
            'total' => $order->total,
            'notes' => $order->notes,
            'cashier_id' => $order->cashier_id,
            'shift_id' => $order->shift_id,
            'flag' => $order->flag,
            'tax_amount' => $order->tax_amount,
            'completed_at' => now(),
            'original_order_id' => $order->id,
            'created_by' => $order->created_by,
            'paid_by' => auth()->id(),
            'payment_method' => $primaryPayment ? $primaryPayment->getRawOriginal('method') : null,
            'payment_amount' => $primaryPayment ? $primaryPayment->amount : 0,
            'payment_received' => $primaryPayment ? $primaryPayment->received_amount : 0,
            'payment_change' => $primaryPayment ? $primaryPayment->change_amount : 0,
            'payment_reference' => $primaryPayment ? $primaryPayment->reference_number : null,
            'payment_at' => $primaryPayment ? $primaryPayment->processed_at : now(),
        ]);

        // Copy order items to temp_order_items
        foreach ($order->items as $item) {
            TempOrderItem::create([
                'temp_order_id' => $tempOrder->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'modifiers' => $item->modifiers,
                'notes' => $item->notes,
            ]);
        }

        // Force delete the original order from orders table
        // (cascade will also remove order_items and payments from DB)
        $order->forceDelete();

        \Log::info('Order moved to temp_orders', [
            'original_order_id' => $order->id,
            'temp_order_id' => $tempOrder->id,
            'order_number' => $order->order_number,
            'flag' => $order->flag,
        ]);

        return $tempOrder;
    }
}
