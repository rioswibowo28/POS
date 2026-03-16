@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Today Sales -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">
                    Rp {{ number_format($todaySales, 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Today's Orders</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $todayOrders }}</p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-receipt text-2xl text-blue-600"></i>
            </div>
        </div>
    </div>

    <!-- Completed Orders -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Completed Today</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $completedToday }}</p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <i class="fas fa-check-circle text-2xl text-purple-600"></i>
            </div>
        </div>
    </div>

    <!-- Occupied Tables -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Occupied Tables</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $occupiedTables }}/{{ $totalTables }}</p>
            </div>
            <div class="bg-orange-100 rounded-full p-3">
                <i class="fas fa-table text-2xl text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($recentOrders as $order)
                    <div class="flex items-center justify-between pb-4 border-b last:border-0">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-sm text-gray-500">
                                @if($order->table)
                                    Table {{ $order->table->number }} 
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $order->type->value)) }}
                                @endif
                                • {{ $order->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($order->status->value === 'completed') bg-green-100 text-green-800
                                @elseif($order->status->value === 'processing') bg-blue-100 text-blue-800
                                @elseif($order->status->value === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($order->status->value) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">No orders yet</p>
                @endforelse
            </div>
            
            <div class="mt-4">
                <a href="{{ route('orders.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                    View all orders <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Popular Products -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Popular Products</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($popularProducts as $product)
                    <div class="flex items-center justify-between pb-4 border-b last:border-0">
                        <div class="flex items-center flex-1">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-lg">
                                @else
                                    <i class="fas fa-utensils text-gray-400"></i>
                                @endif
                            </div>
                            <div class="ml-4">
                                <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-sm text-gray-500">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">{{ $product->total_sold ?? 0 }}</p>
                            <p class="text-xs text-gray-500">sold</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">No products sold yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
