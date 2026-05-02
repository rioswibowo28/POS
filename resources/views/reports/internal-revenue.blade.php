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
                  <div class="flex-1 min-w-[150px]">
                      <label class="block text-sm font-medium text-gray-700 mb-2">Shift</label>
                      <select name="shift_id" class="input">
                          <option value="all" {{ $shiftId == 'all' ? 'selected' : '' }}>Semua Shift</option>
                          @foreach($masterShifts as $ms)
                              <option value="{{ $ms->id }}" {{ $shiftId == $ms->id ? 'selected' : '' }}>{{ $ms->name }}</option>
                          @endforeach
                      </select>
                  </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Pembayaran</label>
                    <select name="payment_type" class="input">
                        <option value="all" {{ ($paymentType ?? 'all') == 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="cash" {{ ($paymentType ?? 'all') == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="qris" {{ ($paymentType ?? 'all') == 'qris' ? 'selected' : '' }}>QRIS</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search mr-2"></i> Tampilkan
                    </button>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <a href="{{ route('reports.internal-revenue.print', ['start_date' => $startDate, 'end_date' => $endDate, 'shift_id' => $shiftId, 'payment_type' => $paymentType ?? 'all']) }}" target="_blank" class="btn-secondary inline-flex items-center">
                    <i class="fas fa-print mr-2"></i> Cetak
                </a>
                <a href="{{ route('reports.internal-revenue.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'shift_id' => $shiftId, 'payment_type' => $paymentType ?? 'all']) }}" class="btn-secondary inline-flex items-center" style="background-color: #dc2626; color: white; border-color: #dc2626;">
                    <i class="fas fa-file-pdf mr-2"></i> PDF
                </a>
                <a href="{{ route('reports.internal-revenue.export-excel', ['start_date' => $startDate, 'end_date' => $endDate, 'shift_id' => $shiftId, 'payment_type' => $paymentType ?? 'all']) }}" class="btn-secondary inline-flex items-center" style="background-color: #16a34a; color: white; border-color: #16a34a;">
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Bill</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Order</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tunai</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">QRIS</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sumber</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diskon</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($allOrders as $i => $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->business_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->created_at->format('H:i') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->shift->masterShift->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $order->bill_number }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->order_number }}</td>
                                @php
                                    $cashAmount = 0;
                                    $qrisAmount = 0;
                                    if (($order->_source ?? '') === 'order' && isset($order->payments)) {
                                        $cashAmount = $order->payments->where('method.value', 'cash')->sum('amount');
                                        $qrisAmount = $order->payments->where('method.value', 'qris')->sum('amount');
                                    } elseif (($order->_source ?? '') === 'temp') {
                                        
                                        if (strtolower($order->payment_method) === 'cash') {
                                            $cashAmount = $order->total;
                                        } elseif (strtolower($order->payment_method) === 'qris') {
                                            $qrisAmount = $order->total;
                                        } else {
                                            if (!empty($order->payment_reference) && str_starts_with(trim($order->payment_reference), '{')) {
                                                $ref = json_decode($order->payment_reference, true);
                                                $cashAmount = $ref['split_breakdown']['cash'] ?? 0;
                                                $qrisAmount = $ref['split_breakdown']['qris'] ?? 0;
                                            } elseif (str_contains(strtolower($order->payment_method), 'cash') && str_contains(strtolower($order->payment_method), 'qris')) {
                                                $cashAmount = $order->total / 2;
                                                $qrisAmount = $order->total / 2;
                                            }
                                        }
                                    }
                                @endphp
                                <td class="px-4 py-3 text-sm text-right">{{ $cashAmount > 0 ? 'Rp ' . number_format($cashAmount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right">{{ $qrisAmount > 0 ? 'Rp ' . number_format($qrisAmount, 0, ',', '.') : '-' }}</td>
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
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="printReceipt({{ $order->original_order_id ?? $order->id }})" class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition" title="Print Receipt">
                                            <i class="fas fa-print text-xs"></i>
                                        </button>
                                        @if(isset($order->original_order_id) || $order instanceof \App\Models\TempOrder)
                                        <form action="{{ route('temp-orders.cancel', $order->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this temp order?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition" title="Cancel Temp Order">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-green-50 font-bold">
                            @php
                                $allCashTotal = 0;
                                $allQrisTotal = 0;
                                foreach($allOrders as $order) {
                                    if (($order->_source ?? '') === 'order' && isset($order->payments)) {
                                        $allCashTotal += $order->payments->where('method.value', 'cash')->sum('amount');
                                        $allQrisTotal += $order->payments->where('method.value', 'qris')->sum('amount');
                                    } elseif (($order->_source ?? '') === 'temp') {
                                        if (strtolower($order->payment_method) === 'cash') {
                                            $allCashTotal += $order->total;
                                        } elseif (strtolower($order->payment_method) === 'qris') {
                                            $allQrisTotal += $order->total;
                                        } else {
                                            if (!empty($order->payment_reference) && str_starts_with(trim($order->payment_reference), '{')) {
                                                $ref = json_decode($order->payment_reference, true);
                                                $allCashTotal += $ref['split_breakdown']['cash'] ?? 0;
                                                $allQrisTotal += $ref['split_breakdown']['qris'] ?? 0;
                                            } elseif (str_contains(strtolower($order->payment_method), 'cash') && str_contains(strtolower($order->payment_method), 'qris')) {
                                                $allCashTotal += $order->total / 2;
                                                $allQrisTotal += $order->total / 2;
                                            }
                                        }
                                    }
                                }
                            @endphp
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-right text-sm">Subtotal All</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">{{ $allCashTotal > 0 ? 'Rp ' . number_format($allCashTotal, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-blue-600">{{ $allQrisTotal > 0 ? 'Rp ' . number_format($allQrisTotal, 0, ',', '.') : '-' }}</td>
                                <td></td>
                                <td class="px-4 py-3 text-right text-sm">Rp {{ number_format($summary['all_subtotal'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">Rp {{ number_format($summary['all_tax'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-red-600">{{ $summary['all_discount'] > 0 ? '- Rp ' . number_format($summary['all_discount'], 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">Rp {{ number_format($summary['all_total'], 0, ',', '.') }}</td>
                                <td></td>
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Bill</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Order</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tunai</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">QRIS</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diskon</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($normalOrders as $i => $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->business_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->created_at->format('H:i') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->shift->masterShift->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $order->bill_number }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->order_number }}</td>
                                @php
                                    $cashAmount = isset($order->payments) ? $order->payments->where('method.value', 'cash')->sum('amount') : 0;
                                    $qrisAmount = isset($order->payments) ? $order->payments->where('method.value', 'qris')->sum('amount') : 0;
                                @endphp
                                <td class="px-4 py-3 text-sm text-right">{{ $cashAmount > 0 ? 'Rp ' . number_format($cashAmount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right">{{ $qrisAmount > 0 ? 'Rp ' . number_format($qrisAmount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-600">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-red-600">{{ $order->discount > 0 ? '- Rp ' . number_format($order->discount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="printReceipt({{ $order->original_order_id ?? $order->id }})" class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition" title="Print Receipt">
                                            <i class="fas fa-print text-xs"></i>
                                        </button>
                                        @if(isset($order->original_order_id) || $order instanceof \App\Models\TempOrder)
                                        <form action="{{ route('temp-orders.cancel', $order->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this temp order?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition" title="Cancel Temp Order">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-blue-50 font-bold">
                            @php
                                $normalCashTotal = 0;
                                $normalQrisTotal = 0;
                                foreach($normalOrders as $order) {
                                    if(isset($order->payments)) {
                                        $normalCashTotal += $order->payments->where('method.value', 'cash')->sum('amount');
                                        $normalQrisTotal += $order->payments->where('method.value', 'qris')->sum('amount');
                                    }
                                }
                            @endphp
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-right text-sm">Subtotal Normal</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">{{ $normalCashTotal > 0 ? 'Rp ' . number_format($normalCashTotal, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-blue-600">{{ $normalQrisTotal > 0 ? 'Rp ' . number_format($normalQrisTotal, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm">Rp {{ number_format($normalOrders->sum('subtotal'), 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">Rp {{ number_format($normalOrders->sum('tax_amount'), 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-red-600">{{ $normalOrders->sum('discount') > 0 ? '- Rp ' . number_format($normalOrders->sum('discount'), 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-blue-600">Rp {{ number_format($normalOrders->sum('total'), 0, ',', '.') }}</td>
                                <td></td>
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Bill</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Order</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tunai</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">QRIS</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PPN</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diskon</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($tempOrders as $i => $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->business_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->created_at->format('H:i') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->shift->masterShift->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $order->bill_number }}</td>
                                <td class="px-4 py-3 text-sm">{{ $order->order_number }}</td>
                                @php
                                    $cashAmount = 0;
                                    $qrisAmount = 0;
                                    
                                    if (strtolower($order->payment_method) === 'cash') {
                                        $cashAmount = $order->total;
                                    } elseif (strtolower($order->payment_method) === 'qris') {
                                        $qrisAmount = $order->total;
                                    } else {
                                        if (!empty($order->payment_reference) && str_starts_with(trim($order->payment_reference), '{')) {
                                            $ref = json_decode($order->payment_reference, true);
                                            $cashAmount = $ref['split_breakdown']['cash'] ?? 0;
                                            $qrisAmount = $ref['split_breakdown']['qris'] ?? 0;
                                        } elseif (str_contains(strtolower($order->payment_method), 'cash') && str_contains(strtolower($order->payment_method), 'qris')) {
                                            $cashAmount = $order->total / 2;
                                            $qrisAmount = $order->total / 2;
                                        }
                                    }@endphp
                                <td class="px-4 py-3 text-sm text-right">{{ $cashAmount > 0 ? 'Rp ' . number_format($cashAmount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right">{{ $qrisAmount > 0 ? 'Rp ' . number_format($qrisAmount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-600">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-red-600">{{ $order->discount > 0 ? '- Rp ' . number_format($order->discount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="printReceipt({{ $order->original_order_id ?? $order->id }})" class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition" title="Print Receipt">
                                            <i class="fas fa-print text-xs"></i>
                                        </button>
                                        @if(isset($order->original_order_id) || $order instanceof \App\Models\TempOrder)
                                        <form action="{{ route('temp-orders.cancel', $order->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this temp order?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition" title="Cancel Temp Order">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-purple-50 font-bold">
                            @php
                                $tempCashTotal = 0;
                                $tempQrisTotal = 0;
                                foreach($tempOrders as $order) {
                                    
                                    if (strtolower($order->payment_method) === 'cash') {
                                        $tempCashTotal += $order->total;
                                    } elseif (strtolower($order->payment_method) === 'qris') {
                                        $tempQrisTotal += $order->total;
                                    } else {
                                        if (!empty($order->payment_reference) && str_starts_with(trim($order->payment_reference), '{')) {
                                            $ref = json_decode($order->payment_reference, true);
                                            $tempCashTotal += $ref['split_breakdown']['cash'] ?? 0;
                                            $tempQrisTotal += $ref['split_breakdown']['qris'] ?? 0;
                                        } elseif (str_contains(strtolower($order->payment_method), 'cash') && str_contains(strtolower($order->payment_method), 'qris')) {
                                            $tempCashTotal += $order->total / 2;
                                            $tempQrisTotal += $order->total / 2;
                                        }
                                    }}
                            @endphp
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-right text-sm">Subtotal Other</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">{{ $tempCashTotal > 0 ? 'Rp ' . number_format($tempCashTotal, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-blue-600">{{ $tempQrisTotal > 0 ? 'Rp ' . number_format($tempQrisTotal, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm">Rp {{ number_format($tempOrders->sum('subtotal'), 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600">Rp {{ number_format($tempOrders->sum('tax_amount'), 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-red-600">{{ $tempOrders->sum('discount') > 0 ? '- Rp ' . number_format($tempOrders->sum('discount'), 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-purple-600">Rp {{ number_format($tempOrders->sum('total'), 0, ',', '.') }}</td>
                                <td></td>
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
                        <th class="px-4 py-3 text-right text-xs font-medium text-blue-600 uppercase">Tunai</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-blue-600 uppercase">QRIS</th>
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
                        <td class="px-4 py-3 text-sm text-right text-blue-700">Rp {{ number_format($summary['normal_cash'] ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-blue-700">Rp {{ number_format($summary['normal_qris'] ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-bold">Rp {{ number_format($summary['normal_total'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium"><span class="inline-block w-2 h-2 bg-purple-500 rounded-full mr-2"></span>Other Transaction</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $summary['temp_count'] }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['temp_subtotal'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['temp_tax'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($summary['temp_discount'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-blue-700">Rp {{ number_format($summary['temp_cash'] ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-blue-700">Rp {{ number_format($summary['temp_qris'] ?? 0, 0, ',', '.') }}</td>
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
                        <td class="px-4 py-3 text-sm text-right text-blue-800">Rp {{ number_format($summary['all_cash'] ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-blue-800">Rp {{ number_format($summary['all_qris'] ?? 0, 0, ',', '.') }}</td>
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

    function printReceipt(orderId) {
        if (!orderId) return;
        const printWindow = window.open(`/orders/${orderId}/receipt`, '_blank', 'width=800,height=600');
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


