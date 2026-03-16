<?php

namespace App\Http\Controllers\Api;

use App\Services\OrderService;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Get active orders (flag=0 only for tax compliance)
     * Pihak ketiga hanya melihat order normal (kena pajak)
     */
    public function index()
    {
        try {
            $orders = Order::where('flag', false)
                ->whereIn('status', [OrderStatus::PENDING, OrderStatus::PROCESSING])
                ->with(['table', 'items.product', 'items.variant'])
                ->get();
            return $this->sendResponse($orders, 'Active orders retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving orders', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get today's orders (flag=0 only)
     */
    public function today()
    {
        try {
            $orders = Order::where('flag', false)
                ->whereDate('created_at', today())
                ->with(['table', 'items', 'payments'])
                ->get();
            return $this->sendResponse($orders, 'Today orders retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving today orders', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get completed orders (flag=0 only)
     */
    public function completed(Request $request)
    {
        try {
            $query = Order::where('flag', false)
                ->where('status', OrderStatus::COMPLETED);

            if ($request->start_date) {
                $query->whereDate('completed_at', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $query->whereDate('completed_at', '<=', $request->end_date);
            }

            $orders = $query->with(['table', 'items', 'payments'])->get();
            return $this->sendResponse($orders, 'Completed orders retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving completed orders', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get order by table (flag=0 only)
     */
    public function byTable($tableId)
    {
        try {
            $order = Order::where('flag', false)
                ->where('table_id', $tableId)
                ->whereIn('status', [OrderStatus::PENDING, OrderStatus::PROCESSING])
                ->with(['items.product', 'items.variant'])
                ->first();
            return $this->sendResponse($order, 'Order retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving order', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get order by order number (flag=0 only)
     */
    public function byOrderNumber($orderNumber)
    {
        try {
            $order = Order::where('flag', false)
                ->where('order_number', $orderNumber)
                ->with(['table', 'items.product', 'items.variant', 'payments'])
                ->first();

            if (!$order) {
                return $this->sendError('Order not found', [], 404);
            }

            return $this->sendResponse($order, 'Order retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Order not found', ['error' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table_id' => 'nullable|exists:tables,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'type' => 'required|in:dine_in,takeaway,delivery',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.modifiers' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $order = $this->orderService->createOrder($request->all());
            return $this->sendResponse($order, 'Order created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error creating order', ['error' => $e->getMessage()], 500);
        }
    }

    public function addItem(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'modifiers' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $item = $this->orderService->addOrderItem($orderId, $request->all());
            return $this->sendResponse($item, 'Item added to order successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error adding item to order', ['error' => $e->getMessage()], 500);
        }
    }

    public function updateItem(Request $request, $itemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $item = $this->orderService->updateOrderItem($itemId, $request->all());
            return $this->sendResponse($item, 'Order item updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error updating order item', ['error' => $e->getMessage()], 500);
        }
    }

    public function removeItem($itemId)
    {
        try {
            $this->orderService->removeOrderItem($itemId);
            return $this->sendResponse([], 'Order item removed successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error removing order item', ['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $status = OrderStatus::from($request->status);
            $order = $this->orderService->updateOrderStatus($orderId, $status);
            return $this->sendResponse($order, 'Order status updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error updating order status', ['error' => $e->getMessage()], 500);
        }
    }

    public function cancel($orderId)
    {
        try {
            $order = $this->orderService->cancelOrder($orderId);
            return $this->sendResponse($order, 'Order cancelled successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error cancelling order', ['error' => $e->getMessage()], 500);
        }
    }
}
