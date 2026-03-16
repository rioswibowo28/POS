<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Harian Penjualan - {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</title>
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
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #f0f0f0; border: 1px solid #ddd; padding: 6px 8px; font-size: 10px; text-transform: uppercase; text-align: left; }
        td { border: 1px solid #ddd; padding: 5px 8px; font-size: 11px; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .total-row { background: #f0f0f0; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .tax-note { background: #fffde7; border: 1px solid #fdd835; padding: 8px; margin-bottom: 15px; font-size: 10px; }
        @media print {
            body { padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    @if(empty($isPdf))
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">
            <strong>🖨️ Cetak</strong>
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px;">
            Tutup
        </button>
    </div>
    @endif

    <div class="header">
        <div class="store-name">{{ \App\Models\Setting::get('restaurant_name', \App\Models\Setting::get('store_name', 'RESTO POS')) }}</div>
        <div style="font-size: 10px; color: #666;">{{ \App\Models\Setting::get('store_address', '') }}</div>
        <h1>REKAP HARIAN PENJUALAN</h1>
        @if(empty($isPdf))
        <h2>Untuk Keperluan Pelaporan Pajak (PPN {{ $taxPercentage }}%)</h2>
        @endif
        <div class="period">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
    </div>

    @if(empty($isPdf))
    <div class="tax-note">
        <strong>Catatan:</strong> Rekap harian ini hanya mencakup transaksi penjualan dengan PPN (flag normal). 
        DPP = Dasar Pengenaan Pajak (Subtotal sebelum pajak). PPN dihitung {{ $taxPercentage }}% dari DPP.
    </div>
    @endif

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Hari</div>
            <div class="value">{{ $dailyRecap->count() }} hari</div>
        </div>
        <div class="summary-box">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ number_format($summary['total_orders']) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">DPP (Subtotal)</div>
            <div class="value">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Total PPN</div>
            <div class="value" style="color: green;">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Total Penjualan</div>
            <div class="value" style="color: #1d4ed8;">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th>Tanggal</th>
                <th class="text-center">Jumlah Trx</th>
                <th class="text-right">DPP (Subtotal)</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">PPN ({{ $taxPercentage }}%)</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyRecap as $index => $day)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ \Carbon\Carbon::parse($day['date'])->translatedFormat('l, d F Y') }}</td>
                <td class="text-center">{{ number_format($day['total_orders']) }}</td>
                <td class="text-right">Rp {{ number_format($day['subtotal'], 0, ',', '.') }}</td>
                <td class="text-right">{{ $day['discount'] > 0 ? '- Rp ' . number_format($day['discount'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">Rp {{ number_format($day['tax_amount'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($day['total'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data penjualan pada periode ini</td>
            </tr>
            @endforelse
        </tbody>
        @if($dailyRecap->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL</td>
                <td class="text-center">{{ number_format($summary['total_orders']) }}</td>
                <td class="text-right">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</td>
                <td class="text-right">{{ $summary['discount'] > 0 ? '- Rp ' . number_format($summary['discount'], 0, ',', '.') : '-' }}</td>
                <td class="text-right" style="color: green;">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #1d4ed8;">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | {{ \App\Models\Setting::get('restaurant_name', \App\Models\Setting::get('store_name', 'RESTO POS')) }}
    </div>
</body>
</html>
