<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Table;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TableStatus;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Today's statistics
$todaySales = Payment::whereHas('order', function($q) {
                $q->whereDate('business_date', today());
            })
            ->where('status', PaymentStatus::PAID)
            ->sum('amount');

        $todayOrders = Order::whereDate('business_date', today())->count();

        $completedToday = Order::whereDate('business_date', today())
            ->where('status', OrderStatus::COMPLETED)
            ->count();

        $occupiedTables = Table::where('status', TableStatus::OCCUPIED)->count();
        $totalTables = Table::count();

        // Recent orders (today only)
        $recentOrders = Order::with(['table', 'cashier'])
            ->whereDate('business_date', today())
            ->latest()
            ->take(5)
            ->get();

        // Popular products
        $popularProducts = Product::select('products.*', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->join('order_items', 'products.id', '=', 'order_items.product_id') 
            ->groupBy('products.id')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // Monthly revenue chart data (last 7 days)
        $dailyRevenue = Payment::selectRaw('orders.business_date as date, SUM(payments.amount) as total')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('payments.status', PaymentStatus::PAID)
            ->whereBetween('orders.business_date', [today()->subDays(6), today()])
            ->groupBy('orders.business_date')
            ->orderBy('orders.business_date')
            ->get();
        
        return view('dashboard.index', compact(
            'todaySales',
            'todayOrders',
            'completedToday',
            'occupiedTables',
            'totalTables',
            'recentOrders',
            'popularProducts',
            'dailyRevenue'
        ));
    }
}
