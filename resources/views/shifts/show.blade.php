@extends('layouts.app')

@section('title', 'Shift Details')
@section('header', 'Shift Details - ' . $shift->shift_date->format('d M Y'))

@section('content')
<div class="mb-6 flex justify-between items-start">
    <div>
        <div class="flex items-center space-x-3 mb-2">
            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-semibold">
                {{ $shift->masterShift ? $shift->masterShift->name : 'Shift ' . $shift->shift_number }}
            </span>
            @if($shift->status === 'open')
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-semibold">
                <i class="fas fa-circle mr-1 text-xs"></i> Open
            </span>
            @else
            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full font-semibold">
                <i class="fas fa-check mr-1"></i> Closed
            </span>
            @endif
        </div>
        <p class="text-gray-600">
            @if($shift->masterShift)
                {{ substr($shift->masterShift->start_time, 0, 5) }} - {{ substr($shift->masterShift->end_time, 0, 5) }}
            @else
                {{ $shift->shift_number == 1 ? '05:00 - 17:00' : '17:00 - 05:00' }}
            @endif
        </p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('shifts.index') }}" class="group relative inline-flex items-center px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-xl shadow-lg hover:shadow-gray-300 hover:border-gray-400 transition-all duration-300">
            <i class="fas fa-arrow-left mr-2.5 text-lg relative z-10"></i>
            <span class="relative z-10">Kembali</span>
            <span class="absolute inset-0 rounded-xl blur-xl bg-gray-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300 -z-10"></span>
        </a>
        @if($shift->isClosed())
        <a href="{{ route('shifts.print', $shift) }}" target="_blank" class="group relative inline-flex items-center px-6 py-3 bg-white border-2 border-blue-200 text-blue-600 font-semibold rounded-xl shadow-lg hover:shadow-blue-200 transition-all duration-300 overflow-hidden">
            <span class="absolute inset-0 bg-gradient-to-r from-blue-400 to-blue-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
            <i class="fas fa-print mr-2.5 text-lg relative z-10 group-hover:text-white transition-colors duration-300"></i>
            <span class="relative z-10 group-hover:text-white transition-colors duration-300">Cetak Laporan</span>
            <span class="absolute inset-0 rounded-xl blur-xl bg-blue-400 opacity-0 group-hover:opacity-30 transition-opacity duration-300 -z-10"></span>
        </a>
        @else
        <a href="{{ route('shifts.closeForm', $shift) }}" class="group relative inline-flex items-center px-6 py-3 bg-white border-2 border-orange-200 text-orange-600 font-semibold rounded-xl shadow-lg hover:shadow-orange-200 transition-all duration-300 overflow-hidden">
            <span class="absolute inset-0 bg-gradient-to-r from-orange-400 to-orange-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
            <i class="fas fa-times-circle mr-2.5 text-lg relative z-10 group-hover:text-white transition-colors duration-300"></i>
            <span class="relative z-10 group-hover:text-white transition-colors duration-300">Tutup Shift</span>
            <span class="absolute inset-0 rounded-xl blur-xl bg-orange-400 opacity-0 group-hover:opacity-30 transition-opacity duration-300 -z-10"></span>
        </a>
        @endif
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm mb-1">Total Sales</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-100 p-3 rounded-lg">
                <i class="fas fa-money-bill-wave text-green-600 text-2xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm mb-1">Total Orders</p>
                <p class="text-2xl font-bold text-gray-900">{{ $shift->total_orders }}</p>
            </div>
            <div class="bg-blue-100 p-3 rounded-lg">
                <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm mb-1">Opening Cash</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</p>
            </div>
            <div class="bg-purple-100 p-3 rounded-lg">
                <i class="fas fa-wallet text-purple-600 text-2xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm mb-1">Cash Difference</p>
                @if($shift->cash_difference !== null)
                <p class="text-2xl font-bold {{ $shift->cash_difference == 0 ? 'text-green-600' : ($shift->cash_difference > 0 ? 'text-blue-600' : 'text-red-600') }}">
                    {{ $shift->cash_difference >= 0 ? '+' : '' }}Rp {{ number_format($shift->cash_difference, 0, ',', '.') }}
                </p>
                @else
                <p class="text-2xl font-bold text-gray-400">-</p>
                @endif
            </div>
            <div class="bg-yellow-100 p-3 rounded-lg">
                <i class="fas fa-calculator text-yellow-600 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Shift Info -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Shift Information</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Opened By:</span>
                <span class="font-semibold">{{ $shift->openedBy->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Opened At:</span>
                <span class="font-semibold">{{ $shift->opened_at->format('d M Y H:i') }}</span>
            </div>
            @if($shift->closed_at)
            <div class="flex justify-between">
                <span class="text-gray-600">Closed By:</span>
                <span class="font-semibold">{{ $shift->closedBy->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Closed At:</span>
                <span class="font-semibold">{{ $shift->closed_at->format('d M Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Duration:</span>
                <span class="font-semibold">{{ $shift->opened_at->diffForHumans($shift->closed_at, true) }}</span>
            </div>
            @endif
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cash Summary</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Opening Cash:</span>
                <span class="font-semibold">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Cash Payments:</span>
                <span class="font-semibold">Rp {{ number_format($cashPayments->sum('amount'), 0, ',', '.') }}</span>
            </div>
            @if($shift->closed_at)
            <div class="flex justify-between border-t pt-2">
                <span class="text-gray-600">Expected Cash:</span>
                <span class="font-semibold">Rp {{ number_format($shift->expected_cash, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Actual Cash:</span>
                <span class="font-semibold">Rp {{ number_format($shift->closing_cash, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between border-t pt-2">
                <span class="text-gray-900 font-semibold">Difference:</span>
                <span class="font-bold {{ $shift->cash_difference == 0 ? 'text-green-600' : ($shift->cash_difference > 0 ? 'text-blue-600' : 'text-red-600') }}">
                    {{ $shift->cash_difference >= 0 ? '+' : '' }}Rp {{ number_format($shift->cash_difference, 0, ',', '.') }}
                </span>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Payment Methods -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods Breakdown</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="border rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600">Cash</span>
                <i class="fas fa-money-bill-wave text-green-600"></i>
            </div>
            <p class="text-xl font-bold">Rp {{ number_format($cashPayments->sum('amount'), 0, ',', '.') }}</p>
            <p class="text-sm text-gray-500">{{ $cashPayments->count() }} transactions</p>
        </div>
        <div class="border rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600">Non-Cash</span>
                <i class="fas fa-credit-card text-blue-600"></i>
            </div>
            <p class="text-xl font-bold">Rp {{ number_format($nonCashPayments->sum('amount'), 0, ',', '.') }}</p>
            <p class="text-sm text-gray-500">{{ $nonCashPayments->count() }} transactions</p>
        </div>
        <div class="border rounded-lg p-4 bg-primary-50">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-900 font-semibold">Total</span>
                <i class="fas fa-chart-line text-primary-600"></i>
            </div>
            <p class="text-xl font-bold text-primary-600">Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</p>
            <p class="text-sm text-gray-600">{{ $cashPayments->count() + $nonCashPayments->count() }} transactions</p>
        </div>
    </div>
</div>

<!-- Pending Orders -->
@if($pendingOrders->count() > 0)
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
        Pending Orders ({{ $pendingOrders->count() }})
    </h3>
    <div class="space-y-2">
        @foreach($pendingOrders as $order)
        <div class="border rounded-lg p-3 flex justify-between items-center">
            <div>
                <span class="font-semibold">{{ $order->order_number }}</span>
                <span class="text-gray-600 ml-3">{{ $order->table ? 'Table ' . $order->table->number : ucfirst($order->type->value) }}</span>
            </div>
            <div class="text-right">
                <div class="font-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-500">{{ $order->created_at->format('H:i') }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Notes -->
@if($shift->notes)
<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
    <p class="text-gray-600 whitespace-pre-line">{{ $shift->notes }}</p>
</div>
@endif
@endsection
