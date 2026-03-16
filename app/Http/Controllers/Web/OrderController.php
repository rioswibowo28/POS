<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\TableRepository;
use App\Services\OrderService;
use App\Models\TempOrder;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderService $orderService,
        private ProductRepository $productRepository,
        private TableRepository $tableRepository,
        private \App\Repositories\CategoryRepository $categoryRepository
    ) {}

    public function index(Request $request)
    {
        $query = \App\Models\Order::with(['table', 'cashier', 'items']);

        // Search by order number or bill number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(order_number) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(bill_number) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter (default to today)
        $fromDate = $request->input('from_date', now()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));

        $query->whereDate('created_at', '>=', $fromDate)
              ->whereDate('created_at', '<=', $toDate);

        $orders = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('orders.index', compact('orders', 'fromDate', 'toDate'));
    }

    public function receipt($orderId)
    {
        $order = $this->orderRepository
            ->with(['items.product', 'table', 'payments', 'cashier'])
            ->find($orderId);
        
        // If not found in orders, check temp_orders (flag=1 orders moved after payment)
        if (!$order) {
            $tempOrder = TempOrder::with(['items.product', 'table', 'cashier'])
                ->where('original_order_id', $orderId)
                ->first();
            
            if (!$tempOrder) {
                return redirect()->route('orders.index')->with('error', 'Order not found');
            }
            
            return view('orders.receipt-temp', compact('tempOrder'));
        }

        return view('orders.receipt', compact('order'));
    }

    public function cancel($orderId)
    {
        try {
            $this->orderService->cancelOrder($orderId);
            return redirect()->route('orders.index')->with('success', 'Order cancelled successfully');
        } catch (\Exception $e) {
            return redirect()->route('orders.index')->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    public function edit($orderId)
    {
        $order = $this->orderRepository
            ->with(['items', 'table'])
            ->find($orderId);
        
        if (!$order) {
            return redirect()->route('orders.index')->with('error', 'Order not found');
        }

        // Only allow edit for pending orders
        if ($order->status->value !== 'pending') {
            return redirect()->route('orders.index')->with('error', 'Only pending orders can be edited');
        }

        $products = $this->productRepository->with(['category', 'inventory'])->where('is_active', true)->get();
        $categories = $this->categoryRepository->withCount('products')->where('is_active', true)->get();
        $tables = $this->tableRepository->where('is_active', true)->get();

        return view('orders.edit', compact('order', 'products', 'categories', 'tables'));
    }

    public function update($orderId, \Illuminate\Http\Request $request)
    {
        try {
            $order = $this->orderRepository->find($orderId);
            
            if (!$order || $order->status->value !== 'pending') {
                return redirect()->route('orders.index')->with('error', 'Order cannot be updated');
            }
            
            // Simpan table_id lama untuk update status
            $oldTableId = $order->table_id;

            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'table_id' => 'nullable|exists:tables,id',
                'customer_name' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            // Delete old items
            \DB::table('order_items')->where('order_id', $orderId)->delete();

            // Calculate new totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $subtotal += $itemTotal;

                // Create new items
                \DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'name' => \App\Models\Product::find($item['product_id'])->name,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $taxRate = \App\Models\Setting::get('tax_percentage', '10') / 100;
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $tax;

            // Update order
            $this->orderRepository->update($orderId, [
                'table_id' => $validated['table_id'],
                'customer_name' => $validated['customer_name'],
                'notes' => $validated['notes'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);
            
            // Update table status jika meja berubah
            if ($oldTableId != $validated['table_id']) {
                // Set meja lama menjadi available
                if ($oldTableId) {
                    $this->tableRepository->updateStatus($oldTableId, 'available');
                }
                
                // Set meja baru menjadi occupied
                if ($validated['table_id']) {
                    $this->tableRepository->updateStatus($validated['table_id'], 'occupied');
                }
            }

            return redirect()->route('orders.index')->with('success', 'Order updated successfully');
        } catch (\Exception $e) {
            return redirect()->route('orders.index')->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }
}
