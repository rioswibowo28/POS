<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Category;
use App\Models\TempOrder;
use App\Models\Setting;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exports\TaxSalesExport;
use App\Exports\InternalRevenueExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        // Convert to Carbon instances
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Get all report data
        $salesSummary = $this->getSalesSummary($start, $end);
        $topProducts = $this->getTopProducts($start, $end);
        $paymentMethods = $this->getPaymentMethodsBreakdown($start, $end);
        $categorySales = $this->getCategorySales($start, $end);
        $hourlySales = $this->getHourlySales($start, $end);
        $orderStatusBreakdown = $this->getOrderStatusBreakdown($start, $end);
        $cashierPerformance = $this->getCashierPerformance($start, $end);
        
        return view('reports.index', compact(
            'salesSummary',
            'topProducts',
            'paymentMethods',
            'categorySales',
            'hourlySales',
            'orderStatusBreakdown',
            'cashierPerformance',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Get sales summary statistics
     */
    private function getSalesSummary($start, $end)
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->get();
        
        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();
        $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        
        // Count unique customers (based on phone number or name)
        $uniqueCustomers = $orders->filter(function($order) {
            return !empty($order->customer_phone) || !empty($order->customer_name);
        })->unique(function($order) {
            return $order->customer_phone ?: $order->customer_name;
        })->count();
        
        // Calculate totals
        $subtotal = $orders->sum('subtotal');
        $tax = $orders->sum('tax');
        $discount = $orders->sum('discount');
        
        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'avg_order_value' => $avgOrderValue,
            'unique_customers' => $uniqueCustomers,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
        ];
    }
    
    /**
     * Get top selling products
     */
    private function getTopProducts($start, $end, $limit = 10)
    {
        return OrderItem::select(
                'order_items.product_id',
                'order_items.name as product_name',
                'products.category_id',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', OrderStatus::COMPLETED)
            ->groupBy('order_items.product_id', 'order_items.name', 'products.category_id', 'categories.name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get payment methods breakdown
     */
    private function getPaymentMethodsBreakdown($start, $end)
    {
        return Payment::select(
                'method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->whereBetween('created_at', [$start, $end])
            ->where('status', PaymentStatus::PAID)
            ->groupBy('method')
            ->get()
            ->map(function($payment) {
                return [
                    'method' => $payment->method->label(),
                    'count' => $payment->count,
                    'total_amount' => $payment->total_amount,
                    'percentage' => 0, // Will be calculated in view
                ];
            });
    }
    
    /**
     * Get category sales breakdown
     */
    private function getCategorySales($start, $end)
    {
        return OrderItem::select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', OrderStatus::COMPLETED)
            ->whereNotNull('categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();
    }
    
    /**
     * Get hourly sales data for identifying peak hours
     */
    private function getHourlySales($start, $end)
    {
        return Order::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_sales')
            )
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function($item) {
                return [
                    'hour' => sprintf('%02d:00', $item->hour),
                    'order_count' => $item->order_count,
                    'total_sales' => $item->total_sales,
                ];
            });
    }
    
    /**
     * Get order status breakdown
     */
    private function getOrderStatusBreakdown($start, $end)
    {
        return Order::select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total_amount')
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('status')
            ->get()
            ->map(function($order) {
                return [
                    'status' => $order->status->label(),
                    'count' => $order->count,
                    'total_amount' => $order->total_amount,
                ];
            });
    }
    
    /**
     * Get cashier performance
     */
    private function getCashierPerformance($start, $end)
    {
        return Order::select(
                'cashier_id',
                'users.name as cashier_name',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('AVG(total) as avg_order_value')
            )
            ->leftJoin('users', 'orders.cashier_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', OrderStatus::COMPLETED)
            ->whereNotNull('cashier_id')
            ->groupBy('cashier_id', 'users.name')
            ->orderByDesc('total_sales')
            ->get();
    }
    
    /**
     * Export report to Excel/PDF (placeholder for future implementation)
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'excel'); // excel or pdf
        
        // TODO: Implement Excel/PDF export using Laravel Excel or similar package
        
        return response()->json([
            'success' => false,
            'message' => 'Export functionality will be implemented soon'
        ]);
    }

    /**
     * Tax Sales Detail & Recap Report (flag=0 only, for tax/PPN reporting)
     */
    public function taxSales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $taxPercentage = Setting::get('tax_percentage', '10');
        
        // Only flag=0 (normal/with tax) and completed orders
        $orders = Order::with(['items', 'cashier', 'payments'])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->where('flag', false)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $summary = [
            'total_orders' => $orders->count(),
            'subtotal' => $orders->sum('subtotal'),
            'total_tax' => $orders->sum('tax_amount'),
            'discount' => $orders->sum('discount'),
            'total_sales' => $orders->sum('total'),
        ];

        // Daily recap grouped by date
        $dailyRecap = $orders->groupBy(function($order) {
            return $order->created_at->format('Y-m-d');
        })->map(function($dayOrders, $date) {
            return [
                'date' => $date,
                'total_orders' => $dayOrders->count(),
                'subtotal' => $dayOrders->sum('subtotal'),
                'tax_amount' => $dayOrders->sum('tax_amount'),
                'discount' => $dayOrders->sum('discount'),
                'total' => $dayOrders->sum('total'),
            ];
        })->sortKeys()->values();
        
        return view('reports.tax-sales', compact('orders', 'summary', 'dailyRecap', 'taxPercentage', 'startDate', 'endDate'));
    }

    /**
     * Print version of tax sales detail report
     */
    public function taxSalesPrint(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $taxPercentage = Setting::get('tax_percentage', '10');
        
        $orders = Order::with(['items', 'cashier'])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->where('flag', false)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $summary = [
            'total_orders' => $orders->count(),
            'subtotal' => $orders->sum('subtotal'),
            'total_tax' => $orders->sum('tax_amount'),
            'discount' => $orders->sum('discount'),
            'total_sales' => $orders->sum('total'),
        ];
        
        return view('reports.tax-sales-print', compact('orders', 'summary', 'taxPercentage', 'startDate', 'endDate'));
    }

    /**
     * Print version of tax sales daily recap report
     */
    public function taxSalesRecapPrint(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $taxPercentage = Setting::get('tax_percentage', '10');
        
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->where('flag', false)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $summary = [
            'total_orders' => $orders->count(),
            'subtotal' => $orders->sum('subtotal'),
            'total_tax' => $orders->sum('tax_amount'),
            'discount' => $orders->sum('discount'),
            'total_sales' => $orders->sum('total'),
        ];

        $dailyRecap = $orders->groupBy(function($order) {
            return $order->created_at->format('Y-m-d');
        })->map(function($dayOrders, $date) {
            return [
                'date' => $date,
                'total_orders' => $dayOrders->count(),
                'subtotal' => $dayOrders->sum('subtotal'),
                'tax_amount' => $dayOrders->sum('tax_amount'),
                'discount' => $dayOrders->sum('discount'),
                'total' => $dayOrders->sum('total'),
            ];
        })->sortKeys()->values();
        
        return view('reports.tax-sales-recap-print', compact('dailyRecap', 'summary', 'taxPercentage', 'startDate', 'endDate'));
    }

    /**
     * Internal Revenue Report (all flag=0, flag=1, and temp orders)
     */
    public function internalRevenue(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Normal orders (flag=0)
        $normalOrders = Order::with(['cashier', 'payments'])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->where('flag', false)
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Temp orders (from shift closing archive)
        $tempOrders = TempOrder::with(['cashier'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'asc')
            ->get();

        // All transactions combined (normal + temp) sorted by date
        $allOrders = $normalOrders->map(function($order) {
            $order->_source = 'order';
            return $order;
        })->concat($tempOrders->map(function($order) {
            $order->_source = 'temp';
            return $order;
        }))->sortBy('created_at')->values();
        
        $summary = [
            'all_count' => $normalOrders->count() + $tempOrders->count(),
            'all_subtotal' => $normalOrders->sum('subtotal') + $tempOrders->sum('subtotal'),
            'all_tax' => $normalOrders->sum('tax_amount') + $tempOrders->sum('tax_amount'),
            'all_discount' => $normalOrders->sum('discount') + $tempOrders->sum('discount'),
            'all_total' => $normalOrders->sum('total') + $tempOrders->sum('total'),

            'normal_count' => $normalOrders->count(),
            'normal_subtotal' => $normalOrders->sum('subtotal'),
            'normal_tax' => $normalOrders->sum('tax_amount'),
            'normal_discount' => $normalOrders->sum('discount'),
            'normal_total' => $normalOrders->sum('total'),
            
            'temp_count' => $tempOrders->count(),
            'temp_subtotal' => $tempOrders->sum('subtotal'),
            'temp_tax' => $tempOrders->sum('tax_amount'),
            'temp_discount' => $tempOrders->sum('discount'),
            'temp_total' => $tempOrders->sum('total'),
        ];
        
        return view('reports.internal-revenue', compact('allOrders', 'normalOrders', 'tempOrders', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Print version of internal revenue report
     */
    public function internalRevenuePrint(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $normalOrders = Order::with(['cashier', 'payments'])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->where('flag', false)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $tempOrders = TempOrder::with(['cashier'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'asc')
            ->get();

        $allOrders = $normalOrders->map(function($order) {
            $order->_source = 'order';
            return $order;
        })->concat($tempOrders->map(function($order) {
            $order->_source = 'temp';
            return $order;
        }))->sortBy('created_at')->values();
        
        $summary = [
            'all_count' => $normalOrders->count() + $tempOrders->count(),
            'all_subtotal' => $normalOrders->sum('subtotal') + $tempOrders->sum('subtotal'),
            'all_tax' => $normalOrders->sum('tax_amount') + $tempOrders->sum('tax_amount'),
            'all_discount' => $normalOrders->sum('discount') + $tempOrders->sum('discount'),
            'all_total' => $normalOrders->sum('total') + $tempOrders->sum('total'),

            'normal_count' => $normalOrders->count(),
            'normal_subtotal' => $normalOrders->sum('subtotal'),
            'normal_tax' => $normalOrders->sum('tax_amount'),
            'normal_discount' => $normalOrders->sum('discount'),
            'normal_total' => $normalOrders->sum('total'),
            
            'temp_count' => $tempOrders->count(),
            'temp_subtotal' => $tempOrders->sum('subtotal'),
            'temp_tax' => $tempOrders->sum('tax_amount'),
            'temp_discount' => $tempOrders->sum('discount'),
            'temp_total' => $tempOrders->sum('total'),
        ];
        
        return view('reports.internal-revenue-print', compact('allOrders', 'normalOrders', 'tempOrders', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Export Tax Sales to Excel (Detail + Rekap in separate sheets)
     */
    public function taxSalesExportExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $taxPercentage = Setting::get('tax_percentage', '10');
        
        $orders = Order::with(['items', 'cashier', 'payments'])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->where('flag', false)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $summary = [
            'total_orders' => $orders->count(),
            'subtotal' => $orders->sum('subtotal'),
            'total_tax' => $orders->sum('tax_amount'),
            'discount' => $orders->sum('discount'),
            'total_sales' => $orders->sum('total'),
        ];

        $dailyRecap = $orders->groupBy(function($order) {
            return $order->created_at->format('Y-m-d');
        })->map(function($dayOrders, $date) {
            return [
                'date' => $date,
                'total_orders' => $dayOrders->count(),
                'subtotal' => $dayOrders->sum('subtotal'),
                'tax_amount' => $dayOrders->sum('tax_amount'),
                'discount' => $dayOrders->sum('discount'),
                'total' => $dayOrders->sum('total'),
            ];
        })->sortKeys()->values();

        $filename = 'Penjualan_Detail_Rekap_' . $startDate . '_' . $endDate . '.xlsx';

        return (new TaxSalesExport($orders, $dailyRecap, $summary, $taxPercentage, $startDate, $endDate))
            ->download($filename);
    }

    /**
     * Export Tax Sales to PDF
     */
    public function taxSalesExportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $taxPercentage = Setting::get('tax_percentage', '10');
        
        $orders = Order::with(['items', 'cashier', 'payments'])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->where('flag', false)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $summary = [
            'total_orders' => $orders->count(),
            'subtotal' => $orders->sum('subtotal'),
            'total_tax' => $orders->sum('tax_amount'),
            'discount' => $orders->sum('discount'),
            'total_sales' => $orders->sum('total'),
        ];

        $isPdf = true;
        $pdf = Pdf::loadView('reports.tax-sales-print', compact('orders', 'summary', 'taxPercentage', 'startDate', 'endDate', 'isPdf'))
            ->setPaper('a4', 'landscape');

        $filename = 'Penjualan_Detail_' . $startDate . '_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Tax Sales Recap to PDF
     */
    public function taxSalesRecapExportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $taxPercentage = Setting::get('tax_percentage', '10');
        
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->where('flag', false)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $summary = [
            'total_orders' => $orders->count(),
            'subtotal' => $orders->sum('subtotal'),
            'total_tax' => $orders->sum('tax_amount'),
            'discount' => $orders->sum('discount'),
            'total_sales' => $orders->sum('total'),
        ];

        $dailyRecap = $orders->groupBy(function($order) {
            return $order->created_at->format('Y-m-d');
        })->map(function($dayOrders, $date) {
            return [
                'date' => $date,
                'total_orders' => $dayOrders->count(),
                'subtotal' => $dayOrders->sum('subtotal'),
                'tax_amount' => $dayOrders->sum('tax_amount'),
                'discount' => $dayOrders->sum('discount'),
                'total' => $dayOrders->sum('total'),
            ];
        })->sortKeys()->values();

        $isPdf = true;
        $pdf = Pdf::loadView('reports.tax-sales-recap-print', compact('dailyRecap', 'summary', 'taxPercentage', 'startDate', 'endDate', 'isPdf'))
            ->setPaper('a4', 'portrait');

        $filename = 'Rekap_Harian_' . $startDate . '_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Internal Revenue to Excel
     */
    public function internalRevenueExportExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $normalOrders = Order::with(['cashier', 'payments'])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->where('flag', false)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $tempOrders = \App\Models\TempOrder::with(['cashier'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'asc')
            ->get();

        $allOrders = $normalOrders->map(function($order) {
            $order->_source = 'order';
            return $order;
        })->concat($tempOrders->map(function($order) {
            $order->_source = 'temp';
            return $order;
        }))->sortBy('created_at')->values();
        
        $summary = [
            'all_count' => $normalOrders->count() + $tempOrders->count(),
            'all_subtotal' => $normalOrders->sum('subtotal') + $tempOrders->sum('subtotal'),
            'all_tax' => $normalOrders->sum('tax_amount') + $tempOrders->sum('tax_amount'),
            'all_discount' => $normalOrders->sum('discount') + $tempOrders->sum('discount'),
            'all_total' => $normalOrders->sum('total') + $tempOrders->sum('total'),
            'normal_count' => $normalOrders->count(),
            'normal_subtotal' => $normalOrders->sum('subtotal'),
            'normal_tax' => $normalOrders->sum('tax_amount'),
            'normal_discount' => $normalOrders->sum('discount'),
            'normal_total' => $normalOrders->sum('total'),
            'temp_count' => $tempOrders->count(),
            'temp_subtotal' => $tempOrders->sum('subtotal'),
            'temp_tax' => $tempOrders->sum('tax_amount'),
            'temp_discount' => $tempOrders->sum('discount'),
            'temp_total' => $tempOrders->sum('total'),
        ];

        $filename = 'Penjualan_ALL_' . $startDate . '_' . $endDate . '.xlsx';

        return (new InternalRevenueExport($allOrders, $normalOrders, $tempOrders, $summary, $startDate, $endDate))
            ->download($filename);
    }

    /**
     * Export Internal Revenue to PDF
     */
    public function internalRevenueExportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $normalOrders = Order::with(['cashier', 'payments'])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::COMPLETED)
            ->where('flag', false)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $tempOrders = TempOrder::with(['cashier'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'asc')
            ->get();

        $allOrders = $normalOrders->map(function($order) {
            $order->_source = 'order';
            return $order;
        })->concat($tempOrders->map(function($order) {
            $order->_source = 'temp';
            return $order;
        }))->sortBy('created_at')->values();
        
        $summary = [
            'all_count' => $normalOrders->count() + $tempOrders->count(),
            'all_subtotal' => $normalOrders->sum('subtotal') + $tempOrders->sum('subtotal'),
            'all_tax' => $normalOrders->sum('tax_amount') + $tempOrders->sum('tax_amount'),
            'all_discount' => $normalOrders->sum('discount') + $tempOrders->sum('discount'),
            'all_total' => $normalOrders->sum('total') + $tempOrders->sum('total'),

            'normal_count' => $normalOrders->count(),
            'normal_subtotal' => $normalOrders->sum('subtotal'),
            'normal_tax' => $normalOrders->sum('tax_amount'),
            'normal_discount' => $normalOrders->sum('discount'),
            'normal_total' => $normalOrders->sum('total'),
            
            'temp_count' => $tempOrders->count(),
            'temp_subtotal' => $tempOrders->sum('subtotal'),
            'temp_tax' => $tempOrders->sum('tax_amount'),
            'temp_discount' => $tempOrders->sum('discount'),
            'temp_total' => $tempOrders->sum('total'),
        ];

        $isPdf = true;
        $pdf = Pdf::loadView('reports.internal-revenue-print', compact('allOrders', 'normalOrders', 'tempOrders', 'summary', 'startDate', 'endDate', 'isPdf'))
            ->setPaper('a4', 'landscape');

        $filename = 'Penjualan_ALL_' . $startDate . '_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }
}
