@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('header', 'Reports & Analytics')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'overview' }">
    <!-- Date Range Filter -->
    <div class="card">
        <form method="GET" action="{{ route('reports.index') }}" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="input" required>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="input" required>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-search mr-2"></i> Generate Report
            </button>
            <button type="button" onclick="exportReport()" class="btn-secondary">
                <i class="fas fa-download mr-2"></i> Export
            </button>
        </form>
    </div>
    
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button @click="activeTab = 'overview'" 
                    :class="activeTab === 'overview' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-chart-line mr-2"></i>Overview
            </button>
            <button @click="activeTab = 'products'" 
                    :class="activeTab === 'products' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-box mr-2"></i>Products
            </button>
            <button @click="activeTab = 'categories'" 
                    :class="activeTab === 'categories' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-tags mr-2"></i>Categories
            </button>
            <button @click="activeTab = 'payments'" 
                    :class="activeTab === 'payments' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-credit-card mr-2"></i>Payments
            </button>
            <button @click="activeTab = 'performance'" 
                    :class="activeTab === 'performance' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-user-chart mr-2"></i>Performance
            </button>
        </nav>
    </div>
    
    <!-- Overview Tab -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($salesSummary['total_sales'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $salesSummary['total_orders'] }} orders</p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-2xl text-primary-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($salesSummary['total_orders']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Completed orders</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Avg. Order Value</p>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($salesSummary['avg_order_value'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1">Per transaction</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Unique Customers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($salesSummary['unique_customers']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">In this period</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Revenue Breakdown -->
        <div class="card">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Revenue Breakdown</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="border-l-4 border-blue-500 pl-4">
                    <p class="text-sm text-gray-600">Subtotal</p>
                    <p class="text-xl font-bold text-gray-900">Rp {{ number_format($salesSummary['subtotal'], 0, ',', '.') }}</p>
                </div>
                <div class="border-l-4 border-green-500 pl-4">
                    <p class="text-sm text-gray-600">Tax & Service</p>
                    <p class="text-xl font-bold text-gray-900">Rp {{ number_format($salesSummary['tax'], 0, ',', '.') }}</p>
                </div>
                <div class="border-l-4 border-red-500 pl-4">
                    <p class="text-sm text-gray-600">Discount</p>
                    <p class="text-xl font-bold text-gray-900">Rp {{ number_format($salesSummary['discount'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        
        <!-- Hourly Sales Chart -->
        <div class="card">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Hourly Sales Distribution</h3>
            @if($hourlySales->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hour</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $maxSales = $hourlySales->max('total_sales');
                        @endphp
                        @foreach($hourlySales as $hour)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap font-medium">{{ $hour['hour'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $hour['order_count'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">Rp {{ number_format($hour['total_sales'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $maxSales > 0 ? ($hour['total_sales'] / $maxSales * 100) : 0 }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500 text-center py-8">No sales data available for this period</p>
            @endif
        </div>
        
        <!-- Order Status Breakdown -->
        <div class="card">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Order Status Breakdown</h3>
            @if($orderStatusBreakdown->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($orderStatusBreakdown as $status)
                <div class="border rounded-lg p-4">
                    <p class="text-sm text-gray-600">{{ $status['status'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $status['count'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Rp {{ number_format($status['total_amount'], 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-8">No order data available</p>
            @endif
        </div>
    </div>
    
    <!-- Products Tab -->
    <div x-show="activeTab === 'products'" class="space-y-6">
        <div class="card">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Top Selling Products</h3>
            @if($topProducts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Sold</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($topProducts as $index => $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($index === 0)
                                    <span class="w-8 h-8 bg-yellow-100 text-yellow-800 rounded-full inline-flex items-center justify-center font-bold">{{ $index + 1 }}</span>
                                @elseif($index === 1)
                                    <span class="w-8 h-8 bg-gray-100 text-gray-800 rounded-full inline-flex items-center justify-center font-bold">{{ $index + 1 }}</span>
                                @elseif($index === 2)
                                    <span class="w-8 h-8 bg-orange-100 text-orange-800 rounded-full inline-flex items-center justify-center font-bold">{{ $index + 1 }}</span>
                                @else
                                    <span class="w-8 h-8 bg-blue-50 text-blue-800 rounded-full inline-flex items-center justify-center">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium">{{ $product->product_name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $product->category_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ number_format($product->total_quantity) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-green-600">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500 text-center py-8">No product sales data available for this period</p>
            @endif
        </div>
    </div>
    
    <!-- Categories Tab -->
    <div x-show="activeTab === 'categories'" class="space-y-6">
        <div class="card">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Sales by Category</h3>
            @if($categorySales->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items Sold</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">% of Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $totalRevenue = $categorySales->sum('total_revenue');
                        @endphp
                        @foreach($categorySales as $category)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $category->name }}</td>
                            <td class="px-6 py-4">{{ number_format($category->total_quantity) }}</td>
                            <td class="px-6 py-4 font-medium text-green-600">Rp {{ number_format($category->total_revenue, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $percentage = $totalRevenue > 0 ? ($category->total_revenue / $totalRevenue * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="text-sm">{{ number_format($percentage, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500 text-center py-8">No category sales data available for this period</p>
            @endif
        </div>
    </div>
    
    <!-- Payments Tab -->
    <div x-show="activeTab === 'payments'" class="space-y-6">
        <div class="card">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Payment Methods Breakdown</h3>
            @if($paymentMethods->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @php
                    $totalAmount = $paymentMethods->sum('total_amount');
                @endphp
                @foreach($paymentMethods as $payment)
                <div class="border rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-bold text-lg">{{ $payment['method'] }}</h4>
                        <span class="bg-primary-100 text-primary-800 px-3 py-1 rounded-full text-sm font-medium">
                            {{ $payment['count'] }} transactions
                        </span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 mb-2">
                        Rp {{ number_format($payment['total_amount'], 0, ',', '.') }}
                    </p>
                    <div class="flex items-center gap-2 mt-4">
                        <div class="flex-1 bg-gray-200 rounded-full h-3">
                            @php
                                $percentage = $totalAmount > 0 ? ($payment['total_amount'] / $totalAmount * 100) : 0;
                            @endphp
                            <div class="bg-primary-600 h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-sm font-medium">{{ number_format($percentage, 1) }}%</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-8">No payment data available for this period</p>
            @endif
        </div>
    </div>
    
    <!-- Performance Tab -->
    <div x-show="activeTab === 'performance'" class="space-y-6">
        <div class="card">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Cashier Performance</h3>
            @if($cashierPerformance->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cashier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Sales</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg. Order Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($cashierPerformance as $cashier)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $cashier->cashier_name ?? 'Unknown' }}</td>
                            <td class="px-6 py-4">{{ number_format($cashier->total_orders) }}</td>
                            <td class="px-6 py-4 font-medium text-green-600">Rp {{ number_format($cashier->total_sales, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">Rp {{ number_format($cashier->avg_order_value, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500 text-center py-8">No cashier performance data available for this period</p>
            @endif
        </div>
    </div>
</div>

<script>
function exportReport() {
    alert('Export functionality will be available soon. You can implement Excel/PDF export using Laravel Excel or similar package.');
}
</script>
@endsection
