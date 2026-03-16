@extends('layouts.app')

@section('title', 'Orders')
@section('header', 'Order Management')

@section('content')

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
</div>
@endif

<div class="card">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">All Orders</h2>
            <p class="text-gray-600 text-sm">Manage and track all orders</p>
        </div>
        <a href="{{ route('pos.index') }}" class="btn-primary">
            <i class="fas fa-plus mr-2"></i> New Order
        </a>
    </div>
    
    <!-- Filters -->
    <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6 items-stretch">
        <input type="text" name="search" value="{{ request('search') }}" class="input h-[42px]" placeholder="Cari no. order / bill...">
        <select name="type" class="input h-[42px]">
            <option value="">All Types</option>
            <option value="dine_in" {{ request('type') === 'dine_in' ? 'selected' : '' }}>Dine In</option>
            <option value="take_away" {{ request('type') === 'take_away' ? 'selected' : '' }}>Takeaway</option>
            <option value="delivery" {{ request('type') === 'delivery' ? 'selected' : '' }}>Delivery</option>
        </select>
        <select name="status" class="input h-[42px]">
            <option value="">All Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        <input type="text" name="from_date" value="{{ $fromDate }}" class="input h-[42px] flatpickr-date" placeholder="From Date" readonly>
        <input type="text" name="to_date" value="{{ $toDate }}" class="input h-[42px] flatpickr-date" placeholder="To Date" readonly>
        <button type="submit" class="btn-secondary h-[42px]">
            <i class="fas fa-filter mr-2"></i> Filter
        </button>
    </form>
    
    <!-- Orders Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium text-gray-900">{{ $order->order_number }}</div>
                        <div class="text-xs text-gray-500">{{ $order->bill_number }}</div>
                        @if($order->customer_name)
                        <div class="text-sm text-gray-500">{{ $order->customer_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="capitalize text-sm">{{ str_replace('_', ' ', $order->type->value) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($order->table)
                        <span class="text-sm">Table {{ $order->table->number }}</span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm">{{ $order->items->count() }} items</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-medium">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'processing' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800'
                        ];
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$order->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($order->status->value) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex gap-2">
                            <!-- Print Receipt -->
                            <button onclick="printReceipt({{ $order->id }})" 
                               class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition"
                               title="Print Receipt">
                                <i class="fas fa-print text-xs"></i>
                            </button>
                            
                            <!-- Process Payment (Only for pending orders) -->
                            @if($order->status->value === 'pending')
                            <a href="{{ route('orders.payment', $order->id) }}" 
                               class="inline-flex items-center px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition"
                               title="Process Payment">
                                <i class="fas fa-credit-card text-xs"></i>
                            </a>
                            @endif
                            
                            <!-- Edit Order (Only for pending orders) -->
                            @if($order->status->value === 'pending')
                            <a href="{{ route('orders.edit', $order->id) }}"
                               class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 transition"
                               title="Edit Order">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            @endif
                            
                            <!-- Cancel Order (Only for pending/processing orders) -->
                            @if(in_array($order->status->value, ['pending', 'processing']))
                            <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Cancel this order?')">
                                @csrf
                                @method('PUT')
                                <button type="submit"
                                        class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition"
                                        title="Cancel Order">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                        No orders found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($orders->hasPages())
    <div class="mt-6">
        {{ $orders->links() }}
    </div>
    @endif
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.querySelectorAll('.flatpickr-date').forEach(function(el) {
    flatpickr(el, {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd-m-Y',
        allowInput: false
    });
});

function printReceipt(orderId) {
    // Open receipt page in new window
    const printWindow = window.open(`/orders/${orderId}/receipt`, '_blank', 'width=800,height=600');
    
    // Wait for page to load, then trigger print
    if (printWindow) {
        printWindow.onload = function() {
            setTimeout(() => {
                printWindow.print();
            }, 500);
        };
    }
}
</script>
@endpush
@endsection
