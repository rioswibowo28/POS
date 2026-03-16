<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan ALL - {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin-bottom: 4px; }
        .header h2 { font-size: 13px; font-weight: normal; color: #666; }
        .header .period { font-size: 12px; margin-top: 6px; }
        .store-name { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .summary { display: flex; justify-content: space-between; margin-bottom: 15px; gap: 10px; }
        .summary-box { flex: 1; border: 1px solid #ddd; padding: 8px; text-align: center; }
        .summary-box .label { font-size: 10px; color: #666; text-transform: uppercase; }
        .summary-box .value { font-size: 14px; font-weight: bold; margin-top: 3px; }
        .summary-box .count { font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #f0f0f0; border: 1px solid #ddd; padding: 6px 8px; font-size: 10px; text-transform: uppercase; text-align: left; }
        td { border: 1px solid #ddd; padding: 5px 8px; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .total-row { background: #f0f0f0; font-weight: bold; }
        .grand-total { background: #e8f5e9; font-weight: bold; font-size: 12px; }
        .section-title { font-size: 13px; font-weight: bold; margin: 15px 0 8px 0; padding: 5px 8px; }
        .section-all { background: #e8f5e9; border-left: 4px solid #4caf50; }
        .section-normal { background: #e3f2fd; border-left: 4px solid #2196f3; }
        .section-temp { background: #f3e5f5; border-left: 4px solid #9c27b0; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-order { background: #e3f2fd; color: #1565c0; }
        .badge-temp { background: #f3e5f5; color: #7b1fa2; }
        @media print {
            body { padding: 10px; }
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    @if(empty($isPdf))
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">
            <strong>Cetak</strong>
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px;">
            Tutup
        </button>
    </div>
    @endif

    <div class="header">
        <div class="store-name">{{ \App\Models\Setting::get('restaurant_name', \App\Models\Setting::get('store_name', 'RESTO POS')) }}</div>
        <div style="font-size: 10px; color: #666;">{{ \App\Models\Setting::get('store_address', '') }}</div>
        <h1>LAPORAN PENJUALAN ALL</h1>
        <div class="period">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
    </div>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-box" style="border: 2px solid #4caf50;">
            <div class="label">All Transaction</div>
            <div class="count">{{ number_format($summary['all_count']) }} order</div>
            <div class="value" style="color: #2e7d32;">Rp {{ number_format($summary['all_total'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Normal (Order)</div>
            <div class="count">{{ number_format($summary['normal_count']) }} order</div>
            <div class="value" style="color: #1565c0;">Rp {{ number_format($summary['normal_total'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Other Transaction (Temp)</div>
            <div class="count">{{ number_format($summary['temp_count']) }} order</div>
            <div class="value" style="color: #7b1fa2;">Rp {{ number_format($summary['temp_total'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Recap Table -->
    <table>
        <thead>
            <tr>
                <th>Sumber</th>
                <th class="text-center">Jumlah</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">PPN</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Normal (Order)</td>
                <td class="text-center">{{ $summary['normal_count'] }}</td>
                <td class="text-right">Rp {{ number_format($summary['normal_subtotal'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['normal_tax'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['normal_discount'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($summary['normal_total'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Other Transaction (Temp)</td>
                <td class="text-center">{{ $summary['temp_count'] }}</td>
                <td class="text-right">Rp {{ number_format($summary['temp_subtotal'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['temp_tax'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['temp_discount'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($summary['temp_total'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td>GRAND TOTAL</td>
                <td class="text-center">{{ $summary['all_count'] }}</td>
                <td class="text-right">Rp {{ number_format($summary['all_subtotal'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['all_tax'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['all_discount'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['all_total'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- All Transaction Detail -->
    @if($allOrders->count() > 0)
    <div class="section-title section-all">All Transaction - Order + Temp ({{ $allOrders->count() }} transaksi)</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:30px;">No</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>No. Bill</th>
                <th>No. Order</th>
                <th>Pembayaran</th>
                <th class="text-center">Sumber</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">PPN</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allOrders as $i => $order)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td>{{ $order->created_at->format('H:i') }}</td>
                <td class="font-bold">{{ $order->bill_number }}</td>
                <td>{{ $order->order_number }}</td>
                <td>
                    @if(($order->_source ?? '') === 'order' && $order->payments && $order->payments->count() > 0)
                        {{ $order->payments->map(fn($p) => $p->method->label())->join(', ') }}
                    @elseif(($order->_source ?? '') === 'temp' && $order->payment_method)
                        {{ ucfirst($order->payment_method) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge {{ ($order->_source ?? '') === 'temp' ? 'badge-temp' : 'badge-order' }}">{{ ($order->_source ?? '') === 'temp' ? 'Temp' : 'Order' }}</span>
                </td>
                <td class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                <td class="text-right">{{ $order->discount > 0 ? '- Rp ' . number_format($order->discount, 0, ',', '.') : '-' }}</td>
                <td class="text-right font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7" class="text-right">Subtotal All</td>
                <td class="text-right">Rp {{ number_format($summary['all_subtotal'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['all_tax'], 0, ',', '.') }}</td>
                <td class="text-right">{{ $summary['all_discount'] > 0 ? '- Rp ' . number_format($summary['all_discount'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">Rp {{ number_format($summary['all_total'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <!-- Normal Orders Detail -->
    @if($normalOrders->count() > 0)
    <div class="section-title section-normal">Normal - Order ({{ $normalOrders->count() }} transaksi)</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:30px;">No</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>No. Bill</th>
                <th>No. Order</th>
                <th>Pembayaran</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">PPN</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($normalOrders as $i => $order)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td>{{ $order->created_at->format('H:i') }}</td>
                <td class="font-bold">{{ $order->bill_number }}</td>
                <td>{{ $order->order_number }}</td>
                <td>
                    @if($order->payments && $order->payments->count() > 0)
                        {{ $order->payments->map(fn($p) => $p->method->label())->join(', ') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                <td class="text-right">{{ $order->discount > 0 ? '- Rp ' . number_format($order->discount, 0, ',', '.') : '-' }}</td>
                <td class="text-right font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">Subtotal Normal</td>
                <td class="text-right">Rp {{ number_format($normalOrders->sum('subtotal'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($normalOrders->sum('tax_amount'), 0, ',', '.') }}</td>
                <td class="text-right">{{ $normalOrders->sum('discount') > 0 ? '- Rp ' . number_format($normalOrders->sum('discount'), 0, ',', '.') : '-' }}</td>
                <td class="text-right">Rp {{ number_format($normalOrders->sum('total'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <!-- Other Transaction (Temp) Detail -->
    @if($tempOrders->count() > 0)
    <div class="section-title section-temp">Other Transaction - Temp Order ({{ $tempOrders->count() }} transaksi)</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:30px;">No</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>No. Bill</th>
                <th>No. Order</th>
                <th>Pembayaran</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">PPN</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tempOrders as $i => $order)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td>{{ $order->created_at->format('H:i') }}</td>
                <td class="font-bold">{{ $order->bill_number }}</td>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->payment_method ? ucfirst($order->payment_method) : '-' }}</td>
                <td class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                <td class="text-right">{{ $order->discount > 0 ? '- Rp ' . number_format($order->discount, 0, ',', '.') : '-' }}</td>
                <td class="text-right font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">Subtotal Other</td>
                <td class="text-right">Rp {{ number_format($tempOrders->sum('subtotal'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($tempOrders->sum('tax_amount'), 0, ',', '.') }}</td>
                <td class="text-right">{{ $tempOrders->sum('discount') > 0 ? '- Rp ' . number_format($tempOrders->sum('discount'), 0, ',', '.') : '-' }}</td>
                <td class="text-right">Rp {{ number_format($tempOrders->sum('total'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | {{ \App\Models\Setting::get('restaurant_name', \App\Models\Setting::get('store_name', 'RESTO POS')) }}
    </div>
</body>
</html>
