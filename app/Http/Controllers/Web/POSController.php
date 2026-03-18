<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Package;
use App\Models\Product;
use App\Models\Table;
use App\Enums\TableStatus;
use App\Services\OrderService;
use Illuminate\Http\Request;

class POSController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index()
    {
        $categories = Category::where('is_active', true)
            ->withCount('products')
            ->get();
            
        $products = Product::with(['category', 'variants', 'modifiers', 'inventory'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->get();
            
        $tables = Table::where('is_active', true)
            ->with(['currentOrder' => function($query) {
                $query->whereIn('status', ['pending', 'processing']);
            }])
            ->get();

        $packages = Package::with(['items.product'])
            ->where('is_active', true)
            ->get();

        // Order limit (based on total rupiah within time range)
        $orderLimitEnabled = \App\Models\Setting::get('order_limit_enabled', '0') == '1';
        $orderLimitAmount = (int) \App\Models\Setting::get('order_limit_amount', '0');
        $orderLimitStart = \App\Models\Setting::get('order_limit_start', '00:00');
        $orderLimitEnd = \App\Models\Setting::get('order_limit_end', '23:59');
        $todayOrderTotal = (int) \App\Models\Order::whereDate('created_at', today())->sum('total');

        // Check if current time is within limit range
        $now = now()->format('H:i');
        $orderLimitActive = $orderLimitEnabled && $now >= $orderLimitStart && $now <= $orderLimitEnd;
        
        return view('pos.index', compact('categories', 'products', 'tables', 'packages', 'orderLimitEnabled', 'orderLimitAmount', 'orderLimitStart', 'orderLimitEnd', 'orderLimitActive', 'todayOrderTotal'));
    }

    public function createOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:dine_in,take_away,delivery',
                'table_id' => 'nullable|exists:tables,id',
                'customer_name' => 'nullable|string|max:255',
                'customer_phone' => 'nullable|string|max:50',
                'tax' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'flag' => 'nullable|boolean',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.name' => 'required|string',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|integer|min:1',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            \Log::info('Creating order with data:', $validated);
            
            // Conditionally check for an open shift based on settings
            $useShifts = \App\Models\Setting::get('use_shifts', '1') == '1';
            if ($useShifts) {
                $currentShift = \App\Models\Shift::getCurrentShift();
                if (!$currentShift) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No shift is currently open. Please open a shift before creating orders.',
                        'error' => 'No open shift'
                    ], 422);
                }
            }
            
            $order = $this->orderService->createOrder($validated);

            // Check order limit for warning (rupiah-based, time-range aware)
            $limitWarning = null;
            $orderLimitEnabled = \App\Models\Setting::get('order_limit_enabled', '0') == '1';
            if ($orderLimitEnabled) {
                $limitStart = \App\Models\Setting::get('order_limit_start', '00:00');
                $limitEnd = \App\Models\Setting::get('order_limit_end', '23:59');
                $now = now()->format('H:i');
                if ($now >= $limitStart && $now <= $limitEnd) {
                    $orderLimitAmount = (int) \App\Models\Setting::get('order_limit_amount', '0');
                    $todayTotal = (int) \App\Models\Order::whereDate('created_at', today())->sum('total');
                    if ($orderLimitAmount > 0 && $todayTotal >= $orderLimitAmount) {
                        $limitWarning = "Total penjualan hari ini sudah mencapai limit (Rp " . number_format($todayTotal, 0, ',', '.') . " / Rp " . number_format($orderLimitAmount, 0, ',', '.') . ")";
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order,
                'redirect' => route('pos.index'),
                'limit_warning' => $limitWarning,
            ]);
        } catch (\Exception $e) {
            \Log::error('Order creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function customerDisplay()
    {
        $restaurantName = \App\Models\Setting::get('restaurant_name', 'POS Resto');
        $restaurantLogo = \App\Models\Setting::get('restaurant_logo');
        $posterImage = \App\Models\Setting::get('customer_display_poster');
        $displayMode = \App\Models\Setting::get('customer_display_mode', 'local');
        
        // Use relative URL to avoid APP_URL mismatch on network/customer display access
        if ($posterImage) {
            $posterImage = '/storage/' . ltrim($posterImage, '/');
        }
        
        return view('pos.customer-display', compact('restaurantName', 'restaurantLogo', 'posterImage', 'displayMode'));
    }

    public function getCustomerDisplayData()
    {
        $data = \Cache::get('customer_display_data', [
            'cartItems' => [],
            'subtotal' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
            'taxRate' => 0.10,
            'orderType' => '',
            'tableNumber' => '',
            'customerName' => '',
            'mode' => '',
            'orderNumber' => ''
        ]);
        
        return response()->json($data);
    }

    public function updateCustomerDisplayData(Request $request)
    {
        $data = $request->all();
        \Cache::put('customer_display_data', $data, now()->addMinutes(30));
        
        return response()->json(['success' => true]);
    }
}
