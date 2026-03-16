@extends('layouts.app')

@section('title', 'Laporan Penjualan Detail & Rekap')
@section('header', 'Laporan Penjualan Detail & Rekap')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ request('tab', 'detail') }}' }">
    <!-- Date Range Filter -->
    <div class="card">
        <form method="GET" action="{{ route('reports.tax-sales') }}" class="space-y-4">
            <input type="hidden" name="tab" x-bind:value="activeTab">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="input" required>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="input" required>
                </div>
                <div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search mr-2"></i> Tampilkan
                    </button>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <a x-show="activeTab === 'detail'" href="{{ route('reports.tax-sales.print', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn-secondary inline-flex items-center">
                    <i class="fas fa-print mr-2"></i> Cetak Detail
                </a>
                <a x-show="activeTab === 'rekap'" href="{{ route('reports.tax-sales.recap-print', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn-secondary inline-flex items-center">
                    <i class="fas fa-print mr-2"></i> Cetak Rekap
                </a>
                <a x-show="activeTab === 'detail'" href="{{ route('reports.tax-sales.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary inline-flex items-center" style="background-color: #dc2626; color: white; border-color: #dc2626;">
                    <i class="fas fa-file-pdf mr-2"></i> PDF Detail
                </a>
                <a x-show="activeTab === 'rekap'" href="{{ route('reports.tax-sales.recap-export-pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary inline-flex items-center" style="background-color: #dc2626; color: white; border-color: #dc2626;">
                    <i class="fas fa-file-pdf mr-2"></i> PDF Rekap
                </a>
                <a href="{{ route('reports.tax-sales.export-excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary inline-flex items-center" style="background-color: #16a34a; color: white; border-color: #16a34a;">
                    <i class="fas fa-file-excel mr-2"></i> Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card">
            <p class="text-gray-600 text-sm">Total Transaksi</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_orders']) }}</p>
        </div>
        <div class="card">
            <p class="text-gray-600 text-sm">Subtotal (DPP)</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <p class="text-gray-600 text-sm">PPN ({{ $taxPercentage }}%)</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <p class="text-gray-600 text-sm">Total Penjualan</p>
            <p class="text-2xl font-bold text-primary-600">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card">
        <div class="border-b border-gray-200 mb-4">
            <nav class="flex space-x-4" aria-label="Tabs">
                <button @click="activeTab = 'detail'"
                    :class="activeTab === 'detail' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-3 px-4 border-b-2 text-sm transition-colors duration-200">
                    <i class="fas fa-list-alt mr-2"></i> Detail Transaksi
                </button>
                <button @click="activeTab = 'rekap'"
                    :class="activeTab === 'rekap' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-3 px-4 border-b-2 text-sm transition-colors duration-200">
                    <i class="fas fa-calendar-alt mr-2"></i> Rekap Harian
                </button>
            </nav>
        </div>

        <!-- Tab: Detail -->
        <div x-show="activeTab === 'detail'" x-transition>
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-file-invoice mr-2 text-primary-600"></i>
                Detail Penjualan - Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </h3>
            
            @if($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Bill</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Order</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembayaran</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">DPP (Subtotal)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diskon</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN ({{ $taxPercentage }}%)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($orders as $index => $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $order->created_at->format('H:i') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">{{ $order->bill_number }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $order->order_number }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @if($order->payments->count() > 0)
                                    @foreach($order->payments as $payment)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $payment->method->value === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ $payment->method->label() }}</span>
                                    @endforeach
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="max-w-xs">
                                    @foreach($order->items as $item)
                                        <div class="text-xs {{ !$loop->last ? 'mb-1' : '' }}">
                                            {{ $item->name }} x{{ $item->quantity }}
                                            <span class="text-gray-400">@ Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">
                                {{ $order->discount > 0 ? '- Rp ' . number_format($order->discount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-right text-sm uppercase">Total</td>
                            {{-- colspan 7 = No + Tanggal + Jam + Bill + Order + Pembayaran + Item --}}
                            <td class="px-4 py-3 text-right text-sm">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm text-red-600">
                                {{ $summary['discount'] > 0 ? '- Rp ' . number_format($summary['discount'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-green-600">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm text-primary-600">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-gray-500 text-center py-8">Tidak ada data penjualan pada periode ini</p>
            @endif
        </div>

        <!-- Tab: Rekap Harian -->
        <div x-show="activeTab === 'rekap'" x-transition>
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                Rekap Harian - Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </h3>
            
            @if($dailyRecap->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumlah Transaksi</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">DPP (Subtotal)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diskon</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN ({{ $taxPercentage }}%)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($dailyRecap as $index => $day)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                {{ \Carbon\Carbon::parse($day['date'])->translatedFormat('l, d F Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ number_format($day['total_orders']) }} trx
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right">Rp {{ number_format($day['subtotal'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">
                                {{ $day['discount'] > 0 ? '- Rp ' . number_format($day['discount'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600">Rp {{ number_format($day['tax_amount'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-bold">Rp {{ number_format($day['total'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-right text-sm uppercase">Total</td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-200 text-gray-800">
                                    {{ number_format($summary['total_orders']) }} trx
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm text-red-600">
                                {{ $summary['discount'] > 0 ? '- Rp ' . number_format($summary['discount'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-green-600">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm text-primary-600">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-gray-500 text-center py-8">Tidak ada data penjualan pada periode ini</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('input[name="start_date"]', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y', allowInput: true });
        flatpickr('input[name="end_date"]', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y', allowInput: true });
    });
</script>
@endpush
