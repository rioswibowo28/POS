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
                width: 58mm;
            }
            #receipt {
                width: 58mm;
                max-width: 58mm;
                margin: 0 auto;
            }
        }
        
        @page {
            margin: 0;
            size: 58mm auto;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            
            #receipt {
                width: 58mm;
                max-width: 58mm;
                margin: 0;
                padding: 2mm;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            #receipt {
                font-family: 'Courier New', Courier, monospace;
                font-size: 8pt;
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
                font-size: 8pt !important;
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
                font-size: 8pt !important;
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
            $restaurantEmail = \App\Models\Setting::get('restaurant_email', 'info@posresto.com');
            $receiptFooter = \App\Models\Setting::get('receipt_footer', 'Terima kasih atas kunjungan Anda!');
        @endphp
        
        <div class="text-center mb-4 border-b pb-4">
            @if($restaurantLogo)
            <div class="flex justify-center mb-2">
                <img src="{{ asset('storage/' . $restaurantLogo) }}" alt="{{ $restaurantName }}" class="h-20 object-contain">
            </div>
            @endif
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ strtoupper($restaurantName) }}</h1>
            <p class="text-sm text-gray-600">{{ $restaurantAddress }}</p>
            <p class="text-sm text-gray-600">Telp: {{ $restaurantPhone }}</p>
        </div>
        
        <!-- Order Info -->
        <div class="mb-4 pb-4 border-b space-y-1 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Bill Number</span>
                <span class="font-medium">{{ $tempOrder->bill_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Date</span>
                <span class="font-medium">{{ $tempOrder->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Type</span>
                <span class="font-medium capitalize">{{ str_replace('_', ' ', $tempOrder->type->value) }}</span>
            </div>
            @if($tempOrder->table)
            <div class="flex justify-between">
                <span class="text-gray-600">Table</span>
                <span class="font-medium">Table {{ $tempOrder->table->number }}</span>
            </div>
            @endif
            @if($tempOrder->customer_name)
            <div class="flex justify-between">
                <span class="text-gray-600">Customer</span>
                <span class="font-medium">{{ $tempOrder->customer_name }}</span>
            </div>
            @endif
            <div class="flex justify-between">
                <span class="text-gray-600">Cashier</span>
                <span class="font-medium">{{ $tempOrder->cashier->name ?? '-' }}</span>
            </div>
        </div>
        
        <!-- Items -->
        <div class="mb-4 pb-4 border-b">
            <h3 class="font-semibold mb-3 text-sm">Items</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-1">Item</th>
                        <th class="text-center py-1">Qty</th>
                        <th class="text-right py-1">Price</th>
                        <th class="text-right py-1">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tempOrder->items as $item)
                    <tr>
                        <td class="py-1">{{ $item->name }}</td>
                        <td class="text-center py-1">{{ $item->quantity }}</td>
                        <td class="text-right py-1">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="text-right py-1">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Totals -->
        <div class="mb-4 pb-4 border-b space-y-1 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span>Rp {{ number_format($tempOrder->subtotal, 0, ',', '.') }}</span>
            </div>
            @php
                $taxPercentage = \App\Models\Setting::get('tax_percentage', '10');
            @endphp
            <div class="flex justify-between">
                <span class="text-gray-600">Tax ({{ $taxPercentage }}%)</span>
                <span>Rp {{ number_format($tempOrder->tax_amount, 0, ',', '.') }}</span>
            </div>
            @if($tempOrder->discount > 0)
            <div class="flex justify-between text-green-600">
                <span>Discount</span>
                <span>- Rp {{ number_format($tempOrder->discount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between text-lg font-bold pt-2 border-t">
                <span>Total</span>
                <span>Rp {{ number_format($tempOrder->total, 0, ',', '.') }}</span>
            </div>
        </div>
        
        <!-- Payment -->
        @if($tempOrder->payment_method)
        <div class="mb-4 pb-4 border-b space-y-1 text-sm">
            <h3 class="font-semibold mb-2">Payment</h3>
            <div class="flex justify-between">
                <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $tempOrder->payment_method) }}</span>
                <span>Rp {{ number_format($tempOrder->payment_amount, 0, ',', '.') }}</span>
            </div>
            @if($tempOrder->payment_change > 0)
            <div class="flex justify-between text-green-600">
                <span>Change</span>
                <span>Rp {{ number_format($tempOrder->payment_change, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>
        @endif
        
        <!-- Footer -->
        <div class="text-center text-gray-600">
            <p class="mb-2">{{ $receiptFooter }}</p>
            @if($restaurantEmail)
            <p class="text-sm">{{ $restaurantEmail }}</p>
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
