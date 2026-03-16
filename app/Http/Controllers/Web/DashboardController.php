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
        $todaySales = Payment::whereDate('created_at', today())
            ->where('status', PaymentStatus::PAID)
            ->sum('amount');
            
        $todayOrders = Order::whereDate('created_at', today())->count();
        
        $completedToday = Order::whereDate('completed_at', today())
            ->where('status', OrderStatus::COMPLETED)
            ->count();
            
        $occupiedTables = Table::where('status', TableStatus::OCCUPIED)->count();
        $totalTables = Table::count();
        
        // Recent orders (today only)
        $recentOrders = Order::with(['table', 'cashier'])
            ->whereDate('created_at', today())
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
        $dailyRevenue = Payment::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->where('status', PaymentStatus::PAID)
            ->whereBetween('created_at', [now()->subDays(6), now()])
            ->groupBy('date')
            ->orderBy('date')
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
