@extends('layouts.app')

@section('title', 'Laporan Penjualan ALL')
@section('header', 'Laporan Penjualan ALL')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="space-y-6" x-data="{ tab: '{{ request('tab', 'all') }}' }">
    <!-- Date Range Filter -->
    <div class="card">
        <form method="GET" action="{{ route('reports.internal-revenue') }}" class="space-y-4">
            <input type="hidden" name="tab" x-bind:value="tab">
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
                <a href="{{ route('reports.internal-revenue.print', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn-secondary inline-flex items-center">
                    <i class="fas fa-print mr-2"></i> Cetak
                </a>
                <a href="{{ route('reports.internal-revenue.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary inline-flex items-center" style="background-color: #dc2626; color: white; border-color: #dc2626;">
                    <i class="fas fa-file-pdf mr-2"></i> PDF
                </a>
                <a href="{{ route('reports.internal-revenue.export-excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary inline-flex items-center" style="background-color: #16a34a; color: white; border-color: #16a34a;">
                    <i class="fas fa-file-excel mr-2"></i> Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card border-l-4 border-green-500">
            <p class="text-gray-600 text-sm">All Transaction</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($summary['all_count']) }} order</p>
            <p class="text-lg font-bold text-green-600 mt-1">Rp {{ number_format($summary['all_total'], 0, ',', '.') }}</p>
        </div>
        <div class="card border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm">Normal</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($summary['normal_count']) }} order</p>
            <p class="text-lg font-bold text-blue-600 mt-1">Rp {{ number_format($summary['normal_total'], 0, ',', '.') }}</p>
        </div>
        <div class="card border-l-4 border-purple-500">
            <p class="text-gray-600 text-sm">Other Transaction</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($summary['temp_count']) }} order</p>
            <p class="text-lg font-bold text-purple-600 mt-1">Rp {{ number_format($summary['temp_total'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Breakdown Detail -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card">
            <h4 class="text-sm font-bold text-gray-700 mb-2">Subtotal Keseluruhan</h4>
            <p class="text-xl font-bold">Rp {{ number_format($summary['all_subtotal'], 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <h4 class="text-sm font-bold text-gray-700 mb-2">Total PPN</h4>
            <p class="text-xl font-bold text-green-600">Rp {{ number_format($summary['all_tax'], 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <h4 class="text-sm font-bold text-gray-700 mb-2">Total Diskon</h4>
            <p class="text-xl font-bold text-red-600">Rp {{ number_format($summary['all_discount'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Tabs -->
    <div>
        <div class="border-b border-gray-200 mb-4">
            <nav class="-mb-px flex space-x-8">
                <button @click="tab = 'all'"
                        :class="tab === 'all' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-list mr-1"></i> All Transaction
                </button>
                <button @click="tab = 'normal'"
                        :class="tab === 'normal' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-receipt mr-1"></i> Normal
                </button>
                <button @click="tab = 'other'"
                        :class="tab === 'other' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-archive mr-1"></i> Other Transaction
                </button>
            </nav>
        </div>

        <!-- Tab: All Transaction -->
        <div x-show="tab === 'all'">
            <div class="card">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                    All Transaction
                </h3>
                @if($allOrders->count() > 0)
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
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sumber</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diskon</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($allOrders as $i => $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->created_at->format('H:i') }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $order->bill_number }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if(($order->_source ?? '') === 'order' && $order->payments && $order->payments->count() > 0)
                                        @foreach($order->payments as $payment)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $payment->method->value === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ $payment->method->label() }}</span>
                                        @endforeach
                                    @elseif(($order->_source ?? '') === 'temp' && $order->payment_method)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ strtolower($order->payment_method) === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ ucfirst($order->payment_method) }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    @if(($order->_source ?? '') === 'temp')
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">Temp</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Order</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-600">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-red-600">{{ $order->discount > 0 ? '- Rp ' . number_format($order->discount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-green-50 font-bold">
                            <tr>
                                <td colspan="7" class="px-4 py-3 text-right text-sm">Subtotal All</td>
                                <td class="px-4 py-3 text-right text-sm">Rp {{ number_format($summary['all_subtotal'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">Rp {{ number_format($summary['all_tax'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-red-600">{{ $summary['all_discount'] > 0 ? '- Rp ' . number_format($summary['all_discount'], 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">Rp {{ number_format($summary['all_total'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <p class="text-gray-500 text-center py-8">Tidak ada data transaksi pada periode ini</p>
                @endif
            </div>
        </div>

        <!-- Tab: Normal -->
        <div x-show="tab === 'normal'">
            <div class="card">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                    Normal
                </h3>
                @if($normalOrders->count() > 0)
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
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diskon</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($normalOrders as $i => $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->created_at->format('H:i') }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $order->bill_number }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($order->payments && $order->payments->count() > 0)
                                        @foreach($order->payments as $payment)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $payment->method->value === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ $payment->method->label() }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-600">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-red-600">{{ $order->discount > 0 ? '- Rp ' . number_format($order->discount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-blue-50 font-bold">
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-right text-sm">Subtotal Normal</td>
                                <td class="px-4 py-3 text-right text-sm">Rp {{ number_format($normalOrders->sum('subtotal'), 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">Rp {{ number_format($normalOrders->sum('tax_amount'), 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-red-600">{{ $normalOrders->sum('discount') > 0 ? '- Rp ' . number_format($normalOrders->sum('discount'), 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-blue-600">Rp {{ number_format($normalOrders->sum('total'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <p class="text-gray-500 text-center py-8">Tidak ada data order normal pada periode ini</p>
                @endif
            </div>
        </div>

        <!-- Tab: Other Transaction -->
        <div x-show="tab === 'other'">
            <div class="card">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <span class="inline-block w-3 h-3 bg-purple-500 rounded-full mr-2"></span>
                    Other Transaction
                </h3>
                @if($tempOrders->count() > 0)
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
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diskon</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($tempOrders as $i => $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->created_at->format('H:i') }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $order->bill_number }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($order->payment_method)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ strtolower($order->payment_method) === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ ucfirst($order->payment_method) }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-600">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-red-600">{{ $order->discount > 0 ? '- Rp ' . number_format($order->discount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-purple-50 font-bold">
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-right text-sm">Subtotal Other</td>
                                <td class="px-4 py-3 text-right text-sm">Rp {{ number_format($tempOrders->sum('subtotal'), 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">Rp {{ number_format($tempOrders->sum('tax_amount'), 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-red-600">{{ $tempOrders->sum('discount') > 0 ? '- Rp ' . number_format($tempOrders->sum('discount'), 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-purple-600">Rp {{ number_format($tempOrders->sum('total'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <p class="text-gray-500 text-center py-8">Tidak ada data other transaction pada periode ini</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Rekap Total -->
    <div class="card bg-gradient-to-r from-green-50 to-blue-50 border-2 border-green-200">
        <h3 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-calculator mr-2 text-green-600"></i>
            Rekap Total Penjualan
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-white/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">Sumber</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase">Jumlah Order</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase">Subtotal</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase">PPN</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase">Diskon</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium"><span class="inline-block w-2 h-2 bg-blue-500 rounded-full mr-2"></span>Normal</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $summary['normal_count'] }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['normal_subtotal'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['normal_tax'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['normal_discount'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-bold">Rp {{ number_format($summary['normal_total'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium"><span class="inline-block w-2 h-2 bg-purple-500 rounded-full mr-2"></span>Other Transaction</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $summary['temp_count'] }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['temp_subtotal'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['temp_tax'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['temp_discount'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-bold">Rp {{ number_format($summary['temp_total'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
                <tfoot class="bg-green-100/50 font-bold text-green-800">
                    <tr>
                        <td class="px-4 py-3 text-sm">GRAND TOTAL</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $summary['all_count'] }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['all_subtotal'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['all_tax'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['all_discount'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-lg">Rp {{ number_format($summary['all_total'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
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
