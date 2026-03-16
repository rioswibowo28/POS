@extends('layouts.app')

@section('title', 'Shift Management')
@section('header', 'Shift Management')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-start">
        <div>
            <p class="text-gray-600">Manage daily shifts and track cashier performance</p>
            @if($currentShift)
            <div class="mt-3 flex items-center space-x-3">
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                    <i class="fas fa-clock mr-1"></i>
                    {{ $currentShift->masterShift ? $currentShift->masterShift->name : 'Shift ' . $currentShift->shift_number }} Active
                </span>
                <span class="text-gray-600 text-sm">
                    Opened by {{ $currentShift->openedBy->name }} at {{ $currentShift->opened_at->format('H:i') }}
                </span>
            </div>
            @endif
        </div>
        <div class="flex space-x-3">
            @if($currentShift)
            <a href="{{ route('shifts.closeForm', $currentShift) }}" class="group relative inline-flex items-center px-6 py-3 bg-white border-2 border-orange-200 text-orange-600 font-semibold rounded-xl shadow-lg hover:shadow-orange-200 transition-all duration-300 overflow-hidden">
                <span class="absolute inset-0 bg-gradient-to-r from-orange-400 to-orange-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                <i class="fas fa-times-circle mr-2.5 text-xl relative z-10 group-hover:text-white transition-colors duration-300"></i>
                <span class="relative z-10 group-hover:text-white transition-colors duration-300">Tutup Shift Saat Ini</span>
                <span class="absolute inset-0 rounded-xl blur-xl bg-orange-400 opacity-0 group-hover:opacity-30 transition-opacity duration-300 -z-10"></span>
            </a>
            @else
            <a href="{{ route('shifts.create') }}" class="btn-primary">
                <i class="fas fa-play mr-2"></i>
                Buka Shift Baru
            </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
    {{ session('error') }}
</div>
@endif

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opened By</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cash Diff</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($shifts as $shift)
            <tr class="{{ $shift->status === 'open' ? 'bg-green-50' : '' }}">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $shift->shift_date->format('d M Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                        {{ $shift->masterShift ? $shift->masterShift->name : 'Shift ' . $shift->shift_number }}
                    </span>
                    <div class="text-xs text-gray-500 mt-1">
                        @if($shift->masterShift)
                            {{ substr($shift->masterShift->start_time, 0, 5) }} - {{ substr($shift->masterShift->end_time, 0, 5) }}
                        @else
                            {{ $shift->shift_number == 1 ? '05:00 - 17:00' : '17:00 - 05:00' }}
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="text-gray-900">{{ $shift->openedBy->name }}</div>
                    <div class="text-xs text-gray-500">{{ $shift->opened_at->format('H:i') }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if($shift->closed_at)
                        {{ $shift->opened_at->diffForHumans($shift->closed_at, true) }}
                    @else
                        <span class="text-green-600">{{ $shift->opened_at->diffForHumans() }}</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    Rp {{ number_format($shift->total_sales, 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $shift->total_orders }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    @if($shift->cash_difference !== null)
                        <span class="{{ $shift->cash_difference == 0 ? 'text-green-600' : ($shift->cash_difference > 0 ? 'text-blue-600' : 'text-red-600') }}">
                            {{ $shift->cash_difference >= 0 ? '+' : '' }}Rp {{ number_format($shift->cash_difference, 0, ',', '.') }}
                        </span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($shift->status === 'open')
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-circle mr-1 text-xs"></i> Open
                    </span>
                    @else
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                        <i class="fas fa-check mr-1"></i> Closed
                    </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="inline-flex items-center space-x-1.5">
                        <a href="{{ route('shifts.show', $shift) }}" class="group relative inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-primary-200 text-primary-600 hover:border-primary-400 transition-all duration-300 shadow-sm hover:shadow-primary-200" title="View Details">
                            <i class="fas fa-eye relative z-10"></i>
                            <span class="absolute inset-0 rounded-xl blur-lg bg-primary-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300 -z-10"></span>
                        </a>
                        @if($shift->isClosed())
                        <a href="{{ route('shifts.print', $shift) }}" target="_blank" class="group relative inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-blue-200 text-blue-600 hover:border-blue-400 transition-all duration-300 shadow-sm hover:shadow-blue-200" title="Print Report">
                            <i class="fas fa-print relative z-10"></i>
                            <span class="absolute inset-0 rounded-xl blur-lg bg-blue-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300 -z-10"></span>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                        <p class="text-lg font-medium">No shifts recorded yet</p>
                        <p class="text-sm">Open a new shift to start tracking sales</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $shifts->links() }}
</div>
@endsection
