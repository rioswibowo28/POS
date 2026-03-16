<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\TableRepository;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $orderRepository;
    protected $tableRepository;

    public function __construct(
        OrderRepository $orderRepository,
        TableRepository $tableRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->tableRepository = $tableRepository;
    }

    public function getActiveOrders()
    {
        return $this->orderRepository->getActive();
    }

    public function getOrderByTable($tableId)
    {
        return $this->orderRepository->getByTable($tableId);
    }

    public function getOrderByNumber($orderNumber)
    {
        return $this->orderRepository->getByOrderNumber($orderNumber);
    }

    public function getTodayOrders()
    {
        return $this->orderRepository->getTodayOrders();
    }

    public function getCompletedOrders($startDate = null, $endDate = null)
    {
        return $this->orderRepository->getCompletedOrders($startDate, $endDate);
    }

    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Get current shift
            $currentShift = \App\Models\Shift::getCurrentShift();
            
            // Handle flag (No Tax Mode)
            $flag = $data['flag'] ?? false;
            $taxRate = $data['tax'] ?? 0;
            
            // Create order
            $orderData = [
                'table_id' => $data['table_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'type' => is_string($data['type']) ? $data['type'] : $data['type']->value,
                'status' => 'pending', // Use string value, will be cast to enum
                'tax' => $taxRate,
                'discount' => $data['discount'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'cashier_id' => auth()->id(),
                'created_by' => auth()->id(),
                'shift_id' => $currentShift ? $currentShift->id : null,
                'flag' => $flag,
                'tax_amount' => 0, // Will be calculated after items are added
            ];

            $order = $this->orderRepository->create($orderData);

            // Add items
            if (isset($data['items']) && count($data['items']) > 0) {
                foreach ($data['items'] as $item) {
                    $this->addOrderItem($order->id, $item);
                }
            }

            // Update table status if table order
            if ($order->table_id) {
                $this->tableRepository->updateStatus($order->table_id, 'occupied');
            }

            // Calculate total with tax logic
            $order->calculateTotal();

            return $order->load(['items.product', 'items.variant', 'table']);
        });
    }

    public function addOrderItem($orderId, array $itemData)
    {
        $order = $this->orderRepository->findOrFail($orderId);

        $item = OrderItem::create([
            'order_id' => $orderId,
            'product_id' => $itemData['product_id'],
            'product_variant_id' => $itemData['product_variant_id'] ?? null,
            'name' => $itemData['name'],
            'price' => $itemData['price'],
            'quantity' => $itemData['quantity'],
            'modifiers' => $itemData['modifiers'] ?? null,
            'notes' => $itemData['notes'] ?? null,
        ]);

        $order->calculateTotal();

        return $item;
    }

    public function updateOrderItem($itemId, array $data)
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update($data);

        $item->order->calculateTotal();

        return $item;
    }

    public function removeOrderItem($itemId)
    {
        $item = OrderItem::findOrFail($itemId);
        $order = $item->order;
        
        $item->delete();
        
        $order->calculateTotal();

        return true;
    }

    public function updateOrderStatus($orderId, OrderStatus $status)
    {
        $order = $this->orderRepository->findOrFail($orderId);
        
        $order->status = $status;
        
        if ($status === OrderStatus::COMPLETED) {
            $order->completed_at = now();
            
            // Update table status if table order
            if ($order->table_id) {
                $this->tableRepository->updateStatus($order->table_id, 'available');
            }
        }
        
        $order->save();

        return $order;
    }

    public function cancelOrder($orderId)
    {
        return DB::transaction(function () use ($orderId) {
            $order = $this->orderRepository->findOrFail($orderId);
            
            $order->status = 'cancelled';
            $order->deleted_by = auth()->id();
            $order->save();

            // Update table status if table order
            if ($order->table_id) {
                $this->tableRepository->updateStatus($order->table_id, 'available');
            }

            return $order;
        });
    }
}
