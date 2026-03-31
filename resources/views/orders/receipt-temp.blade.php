<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #{{ $tempOrder->order_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media screen {
            body {
                background-color: #f3f4f6;
                padding: 20px;
                display: flex;
                justify-content: center;
            }
            .max-w-2xl {
                width: 58mm; /* Sesuaikan tampilan layar dengan ukuran kertas */
            }
            #receipt {
                width: 58mm;
                max-width: 58mm;
                margin: 0 auto;
            }
        }
        
        @page {
            margin: 0;
            size: 58mm auto; /* Spesifik untuk printer 58mm */
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            
            #receipt {
                width: 100%;
                max-width: 48mm; /* Area print efektif dlm kertas 58mm biasanya hanya 48mm */
                margin: 0 auto;
                padding: 0 2mm; 
                box-sizing: border-box;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            #receipt {
                font-family: 'Courier New', Courier, monospace; /* Font printer thermal */
                font-size: 6.5pt;
                line-height: 1.2;
                color: #000;
            }
            
            #receipt h1 {
                font-size: 11pt !important;
                margin-bottom: 2px;
            }
            
            #receipt h3 {
                font-size: 9pt !important;
                margin-bottom: 2px;
            }
            
            #receipt img {
                max-height: 30px !important;
                margin-bottom: 2px;
            }
            
            #receipt table {
                font-size: 6.5pt !important;
                width: 100%;
            }

            #receipt table th, #receipt table td {
                padding: 1px 0 !important;
            }
            
            #receipt .text-2xl {
                font-size: 11pt !important;
            }
            
            #receipt .text-lg {
                font-size: 10pt !important;
            }
            
            #receipt .text-sm {
                font-size: 6.5pt !important;
            }
            
            #receipt .mb-4 {
                margin-bottom: 4px !important;
            }
            
            #receipt .pb-4 {
                padding-bottom: 4px !important;
            }
            
            #receipt .mb-2 {
                margin-bottom: 2px !important;
            }
            
            #receipt * {
                color: #000 !important;
            }
        }
    </style>
</head>
<body>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6" id="receipt">
                                                                                <!-- Header -->
        @php
            $restaurantName = \App\Models\Setting::get('restaurant_name', 'POS Resto');
            $restaurantLogo = \App\Models\Setting::get('restaurant_logo');
            $restaurantAddress = \App\Models\Setting::get('restaurant_address', 'Jl. Contoh No. 123, Jakarta');
            $restaurantPhone = \App\Models\Setting::get('restaurant_phone', '021-12345678');
            $restaurantEmail = \App\Models\Setting::get('restaurant_email', '');
            $receiptFooter = \App\Models\Setting::get('receipt_footer', 'Terima kasih atas kunjungan Anda!');
            $restaurantNpwp = \App\Models\Setting::get('restaurant_npwp', '');
        @endphp
        
        <div id="receipt-content" style="font-size: 6.5pt; font-family: 'Courier New', Courier, monospace; padding-top: 5mm;">
            <div class="text-center" style="margin-bottom: 8px;">
                <div style="font-size: 8.5pt; font-weight: bold;">{{ strtoupper($restaurantName) }}</div>
                <div style="font-size: 7.5pt; font-weight: bold;">{{ strtoupper($restaurantAddress) }}</div>
                @if($restaurantNpwp)
                <div>NPWP : {{ $restaurantNpwp }}</div>
                @endif
            </div>
            
            <div style="border-bottom: 1px dashed #000; margin: 4px 0;"></div>
            
            <!-- Order Info -->
            <div style="display: flex; justify-content: space-between; margin: 4px 0; align-items: flex-start;">
                <div style="display: flex; flex-direction: column; width: 65%;">
                    <span>No Bill :</span>
                    <span>{{ $tempOrder->bill_number }}</span>
                </div>
                <div style="width: 35%; text-align: right;">
                    <span>Kasir :<br>{{ strtoupper(Str::limit($tempOrder->cashier->name, 10)) }}</span>
                </div>
            </div>
            
            <div style="border-bottom: 1px dashed #000; margin: 4px 0;"></div>
            
            <!-- Items -->
            <div style="margin: 4px 0;">
                @php $totalItemCount = 0; @endphp
                @foreach($tempOrder->items as $item)
                @php $totalItemCount += $item->quantity; @endphp
                <div style="display: flex; justify-content: space-between; margin-bottom: 2px; align-items: flex-start;">
                    <span style="flex: 1; padding-right: 2px; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; word-break: break-word;">{{ strtoupper($item->name) }}</span>
                    <span style="width: 15px; text-align: right; flex-shrink: 0;">{{ $item->quantity }}</span>
                    <span style="width: 40px; text-align: right; flex-shrink: 0;">{{ number_format($item->price, 0, ',', '.') }}</span>
                    <span style="width: 50px; text-align: right; flex-shrink: 0;">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            
            <div style="border-bottom: 1px dashed #000; margin: 4px 0;"></div>
            
            <!-- Totals -->
            <div style="margin: 4px 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span style="width: 40%;">Total Item</span>
                    <span style="width: 15%; text-align: left;">{{ $totalItemCount }}</span>
                    <span style="flex: 1; text-align: right;">{{ number_format($tempOrder->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($tempOrder->discount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span style="width: 55%;">Total Disc.</span>
                    <span style="flex: 1; text-align: right;">{{ number_format($tempOrder->discount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span style="width: 55%;">Total Belanja</span>
                    <span style="flex: 1; text-align: right;">{{ number_format($tempOrder->subtotal - $tempOrder->discount, 0, ',', '.') }}</span>
                </div>

                @if($tempOrder->tax_amount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span style="width: 55%;">PPN</span>
                    <span style="flex: 1; text-align: right;">{{ number_format($tempOrder->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span style="width: 55%;">Grand Total</span>
                    <span style="flex: 1; text-align: right;">{{ number_format($tempOrder->total, 0, ',', '.') }}</span>
                </div>
                @endif

                @php
                      $kembali = $tempOrder->payment_change ?? 0;
                  @endphp
                  @if ($tempOrder->payment_method)
                          @php
                              $methodValue = $tempOrder->payment_method;
                              $methodName = strtoupper(str_replace('_', ' ', $methodValue ?? ''));
                              $amountPaid = $tempOrder->payment_received ?? $tempOrder->payment_amount;
                          @endphp
                          <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                              <span style="width: 55%;">{{ $methodName == 'CASH' ? 'TUNAI' : $methodName }}</span>
                              <span style="flex: 1; text-align: right;">{{ number_format($amountPaid, 0, ',', '.') }}</span>
                          </div>
                  @endif
                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span style="width: 55%;">Kembalian</span>
                    <span style="flex: 1; text-align: right;">{{ number_format($kembali, 0, ',', '.') }}</span>
                </div>
            </div>

            <div style="border-bottom: 1px dashed #000; margin: 4px 0;"></div>

            <!-- Footer -->
            <div class="text-center" style="margin-top: 4px; margin-bottom: 16px;">
                <div>Tgl. {{ $tempOrder->created_at->format('d-m-Y H:i:s') }}</div>
                @if($receiptFooter)
                <div style="margin-top: 4px;">{{ $receiptFooter }}</div>
                @endif
            </div>
        </div>
    <!-- Actions -->
    <div class="mt-6 flex flex-col gap-3 no-print">
        <button onclick="window.print()" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-6 rounded-lg transition text-center w-full">
            <i class="fas fa-print mr-2"></i> Print Receipt
        </button>
        <div class="flex gap-2">
            <a href="{{ route('pos.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition flex-1 text-center text-sm">
                <i class="fas fa-plus mr-1"></i> New
            </a>
            <a href="{{ route('orders.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition flex-1 text-center text-sm">
                <i class="fas fa-list mr-1"></i> Orders
            </a>
        </div>
    </div>
</div>

<script>
// Auto-trigger print only if opened directly (not in iframe)
if (window === window.top) {
    window.addEventListener('load', function() {
        setTimeout(() => {
            window.print();
        }, 300);
    });
}
</script>
</body>
</html>





