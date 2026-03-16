<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shift Report - {{ $shift->shift_date->format('d M Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 20mm;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 8px;
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
        .row.total {
            border-top: 1px solid #000;
            border-bottom: 2px double #000;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
        }
        .row.subtotal {
            border-top: 1px dashed #333;
            font-weight: bold;
            margin-top: 3px;
            padding-top: 3px;
        }
        .label {
            flex: 1;
        }
        .value {
            text-align: right;
            font-weight: bold;
        }
        .center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            border-top: 1px dashed #333;
            padding-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table td {
            padding: 3px 5px;
            border-bottom: 1px dotted #ccc;
        }
        @media print {
            body {
                padding: 10mm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ \App\Models\Setting::get('restaurant_name', 'POS RESTO') }}</h1>
        <p>{{ \App\Models\Setting::get('address', '') }}</p>
        <p>{{ \App\Models\Setting::get('phone', '') }}</p>
        <p style="margin-top: 10px; font-size: 14px; font-weight: bold;">SHIFT CLOSING REPORT</p>
    </div>

    <div class="section">
        <div class="row">
            <div class="label">Date</div>
            <div class="value">{{ $shift->shift_date->format('d M Y') }}</div>
        </div>
        <div class="row">
            <div class="label">Shift Number</div>
            <div class="value">Shift {{ $shift->shift_number }} ({{ $shift->shift_number == 1 ? '05:00-17:00' : '17:00-05:00' }})</div>
        </div>
        <div class="row">
            <div class="label">Opened By</div>
            <div class="value">{{ $shift->openedBy->name }}</div>
        </div>
        <div class="row">
            <div class="label">Opened At</div>
            <div class="value">{{ $shift->opened_at->format('H:i') }}</div>
        </div>
        <div class="row">
            <div class="label">Closed By</div>
            <div class="value">{{ $shift->closedBy->name }}</div>
        </div>
        <div class="row">
            <div class="label">Closed At</div>
            <div class="value">{{ $shift->closed_at->format('H:i') }}</div>
        </div>
        <div class="row">
            <div class="label">Duration</div>
            <div class="value">{{ $shift->opened_at->diffForHumans($shift->closed_at, true) }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">SALES SUMMARY</div>
        <div class="row">
            <div class="label">Total Orders</div>
            <div class="value">{{ $shift->total_orders }}</div>
        </div>
        <div class="row total">
            <div class="label">Total Sales</div>
            <div class="value">Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">PAYMENT METHODS</div>
        @foreach($paymentsByMethod as $method => $payments)
        <div class="row">
            <div class="label">{{ ucfirst($method) }}</div>
            <div class="value">Rp {{ number_format($payments->sum('amount'), 0, ',', '.') }} ({{ $payments->count() }}x)</div>
        </div>
        @endforeach
        <div class="row subtotal">
            <div class="label">Cash Payments</div>
            <div class="value">Rp {{ number_format($cashPayments->sum('amount'), 0, ',', '.') }}</div>
        </div>
        <div class="row">
            <div class="label">Non-Cash Payments</div>
            <div class="value">Rp {{ number_format($nonCashPayments->sum('amount'), 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">CASH RECONCILIATION</div>
        <div class="row">
            <div class="label">Opening Cash</div>
            <div class="value">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</div>
        </div>
        <div class="row">
            <div class="label">Cash Sales</div>
            <div class="value">+ Rp {{ number_format($cashPayments->sum('amount'), 0, ',', '.') }}</div>
        </div>
        <div class="row subtotal">
            <div class="label">Expected Cash</div>
            <div class="value">Rp {{ number_format($shift->expected_cash, 0, ',', '.') }}</div>
        </div>
        <div class="row">
            <div class="label">Actual Cash</div>
            <div class="value">Rp {{ number_format($shift->closing_cash, 0, ',', '.') }}</div>
        </div>
        <div class="row total">
            <div class="label">Difference</div>
            <div class="value" style="color: {{ $shift->cash_difference == 0 ? 'green' : ($shift->cash_difference > 0 ? 'blue' : 'red') }}">
                {{ $shift->cash_difference >= 0 ? '+' : '' }}Rp {{ number_format($shift->cash_difference, 0, ',', '.') }}
            </div>
        </div>
    </div>

    @if($shift->notes)
    <div class="section">
        <div class="section-title">NOTES</div>
        <p style="padding: 5px 0; white-space: pre-line;">{{ $shift->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Printed on {{ now()->format('d M Y H:i') }}</p>
        <p style="margin-top: 10px;">Thank you!</p>
        <div style="margin-top: 20px; display: flex; justify-content: space-around;">
            <div style="text-align: center;">
                <div style="border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; width: 150px; margin: 0 auto;">
                    Cashier
                </div>
            </div>
            <div style="text-align: center;">
                <div style="border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; width: 150px; margin: 0 auto;">
                    Manager
                </div>
            </div>
        </div>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
            <i class="fas fa-print"></i> Print Report
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; margin-left: 10px;">
            Close
        </button>
    </div>

    <script>
        // Auto print on load
        window.onload = function() {
            // Uncomment to auto-print
            // window.print();
        }
    </script>
</body>
</html>
