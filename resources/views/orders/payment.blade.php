@extends('layouts.app')

@section('title', 'Payment - Order #' . $order->order_number)
@section('header', 'Payment')

@if($midtransConfigured)
@push('styles')
<!-- Midtrans Snap CSS -->
<link rel="stylesheet" href="https://app.sandbox.midtrans.com/snap/snap.css">
@endpush
@endif

@section('content')
<div x-data="paymentApp()" x-init="init()" class="max-w-4xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Order Summary</h2>
            
            <div class="mb-4 pb-4 border-b">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">Bill Number</span>
                    <span class="font-medium">{{ $order->bill_number }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">Order Number</span>
                    <span class="font-medium">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">Type</span>
                    <span class="font-medium capitalize">{{ str_replace('_', ' ', $order->type->value) }}</span>
                </div>
                @if($order->table)
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">Table</span>
                    <span class="font-medium">Table {{ $order->table->number }}</span>
                </div>
                @endif
                @if($order->customer_name)
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">Customer</span>
                    <span class="font-medium">{{ $order->customer_name }}</span>
                </div>
                @endif
            </div>
            
            <div class="space-y-3 mb-4">
                <h3 class="font-semibold text-gray-900">Items</h3>
                @foreach($order->items as $item)
                <div class="flex justify-between text-sm">
                    <div>
                        <span class="font-medium">{{ $item->name }}</span>
                        <span class="text-gray-500">x{{ $item->quantity }}</span>
                    </div>
                    <span>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            
            <div class="border-t pt-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    @php $taxType = \App\Models\Setting::get('tax_type', 'exclude'); @endphp
                    <span class="text-gray-600">Tax ({{ $order->tax }}%){{ $taxType === 'include' ? ' (incl)' : '' }}</span>
                    <span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                </div>
                @if($order->discount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>Discount</span>
                    <span>- Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-lg font-bold border-t pt-2">
                    <span>Total</span>
                    <span class="text-primary-600">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        
        <!-- Payment Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Payment Method</h2>
            
            <form @submit.prevent="processPayment()">
                <!-- Payment Methods -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <label class="relative cursor-pointer">
                        <input type="radio" x-model="paymentMethod" value="cash" class="sr-only">
                        <div :class="paymentMethod === 'cash' ? 'border-primary-600 bg-primary-50' : 'border-gray-200'"
                             class="border-2 rounded-lg p-4 text-center transition">
                            <i class="fas fa-money-bill-wave text-3xl mb-2" :class="paymentMethod === 'cash' ? 'text-primary-600' : 'text-gray-400'"></i>
                            <p class="font-medium text-sm">Cash</p>
                        </div>
                    </label>
                    
                    <label class="relative cursor-pointer">
                        <input type="radio" x-model="paymentMethod" value="qris" class="sr-only">
                        <div :class="paymentMethod === 'qris' ? 'border-primary-600 bg-primary-50' : 'border-gray-200'"
                             class="border-2 rounded-lg p-4 text-center transition">
                            <i class="fas fa-qrcode text-3xl mb-2" :class="paymentMethod === 'qris' ? 'text-primary-600' : 'text-gray-400'"></i>
                            <p class="font-medium text-sm">QRIS</p>
                        </div>
                    </label>
                </div>
                
                <!-- Amount Paid -->
                  <div class="mb-6 relative" @click.outside="if(!event.target.closest('.keypad-container')) showKeypad = false">
                      <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid</label>
                      <div class="relative">
                          <input type="text"
                                 inputmode="none"
                                 x-model="amountPaidFormatted"
                                 @input="handleAmountInput($event.target.value)"
                                 @focus="showKeypad = true"
                                 class="input text-lg font-bold w-full"
                                 placeholder="0"
                                 required>
                          <button type="button" @click="showKeypad = !showKeypad" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                              <i class="fas fa-keyboard text-xl"></i>
                          </button>
                      </div>

                      <!-- Quick Amount Buttons -->
                      <div class="grid grid-cols-4 gap-2 mt-3">
                          <button type="button" @click="setAmount({{ $order->total }})" class="btn-secondary text-xs py-2">Exact</button>
                          <button type="button" @click="setAmount(50000)" class="btn-secondary text-xs py-2">50k</button>
                          <button type="button" @click="setAmount(100000)" class="btn-secondary text-xs py-2">100k</button>
                          <button type="button" @click="setAmount(200000)" class="btn-secondary text-xs py-2">200k</button>
                      </div>

                      <!-- On-Screen Keypad -->
                      <div x-show="showKeypad" 
                           x-transition
                           class="keypad-container mt-3 bg-gray-50 border border-gray-200 rounded-xl p-3 grid grid-cols-3 gap-2">
                           
                           <template x-for="n in [1,2,3,4,5,6,7,8,9]" :key="n">
                               <button type="button" @click="appendNumber(n)" class="bg-white hover:bg-gray-100 text-gray-800 font-bold text-xl py-3 rounded-lg shadow-sm border border-gray-200" x-text="n"></button>
                           </template>
                           
                           <button type="button" @click="appendNumber('00')" class="bg-white hover:bg-gray-100 text-gray-800 font-bold text-xl py-3 rounded-lg shadow-sm border border-gray-200">00</button>
                           <button type="button" @click="appendNumber(0)" class="bg-white hover:bg-gray-100 text-gray-800 font-bold text-xl py-3 rounded-lg shadow-sm border border-gray-200">0</button>
                           <button type="button" @click="deleteNumber()" class="bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xl py-3 rounded-lg shadow-sm border border-red-200">
                              <i class="fas fa-backspace"></i>
                           </button>
                           <button type="button" @click="clearNumber()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-lg py-2 rounded-lg shadow-sm border border-gray-300 col-span-1">Clear</button>
                           <button type="button" @click="showKeypad = false" class="bg-primary-600 hover:bg-primary-700 text-white font-bold text-lg py-2 rounded-lg shadow-sm col-span-2">Done</button>
                </div>
                
                <!-- Change -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Change</span>
                        <span class="text-2xl font-bold" :class="change >= 0 ? 'text-green-600' : 'text-red-600'" 
                              x-text="`Rp ${formatMoney(change)}`"></span>
                    </div>
                    <template x-if="change < 0">
                        <p class="text-red-600 text-sm mt-2">Insufficient payment amount</p>
                    </template>
                </div>
                
                <!-- Submit Button / Print Button -->
                <template x-if="!paymentCompleted">
                    <button type="submit" 
                            :disabled="change < 0 || processing"
                            :class="change < 0 || processing ? 'bg-gray-300 cursor-not-allowed' : 'bg-primary-600 hover:bg-primary-700'"
                            class="w-full text-white font-semibold py-3 rounded-lg transition">
                        <template x-if="processing">
                            <span><i class="fas fa-spinner fa-spin mr-2"></i> Processing...</span>
                        </template>
                        <template x-if="!processing">
                            <span><i class="fas fa-check-circle mr-2"></i> Complete Payment</span>
                        </template>
                    </button>
                </template>
                
                <template x-if="paymentCompleted">
                    <div class="space-y-3">
                        <div class="bg-green-50 border-2 border-green-500 rounded-lg p-4 text-center">
                            <i class="fas fa-check-circle text-green-600 text-3xl mb-2"></i>
                            <p class="text-green-800 font-bold text-lg">Payment Successful!</p>
                            <p class="text-green-600 text-sm">Order #{{ $order->order_number }}</p>
                        </div>
                        
                        <button type="button" @click="printBill()" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                            <i class="fas fa-print mr-2"></i> Print Bill
                        </button>
                    </div>
                </template>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function paymentApp() {
    return {
        orderTotal: {{ $order->total }},
        paymentMethod: 'cash',
        amountPaid: {{ $order->total }},
        amountPaidFormatted: '{{ number_format($order->total, 0, ',', '.') }}',
        change: 0,
        showKeypad: false,
        processing: false,
        paymentCompleted: false,
        midtransConfigured: {{ $midtransConfigured ? 'true' : 'false' }},
        displayMode: '{{ $displayMode ?? 'local' }}',
        broadcastChannel: null,
        
        init() {
            // Init BroadcastChannel for instant same-browser communication
            if (typeof BroadcastChannel !== 'undefined') {
                try { this.broadcastChannel = new BroadcastChannel('pos_customer_display'); } catch(e) {}
            }
            // Sync order data to customer display
            this.syncToCustomerDisplay();
            this.calculateChange();
        },
        
        syncToCustomerDisplay() {
            const displayData = {
                mode: 'payment',
                orderNumber: '{{ $order->order_number }}',
                orderType: '{{ $order->type->value }}',
                tableNumber: '{{ $order->table ? $order->table->number : "" }}',
                customerName: '{{ $order->customer_name ?? "" }}',
                cartItems: @json($cartItems),
                subtotal: {{ $order->subtotal }},
                tax: {{ $order->tax_amount }},
                discount: {{ $order->discount }},
                total: {{ $order->total }},
                taxRate: {{ $order->tax / 100 }}
            };
            localStorage.setItem('pos_customer_display', JSON.stringify(displayData));
            if (this.broadcastChannel) {
                try { this.broadcastChannel.postMessage(displayData); } catch(e) {}
            }
            if (this.displayMode === 'network') {
                fetch('/api/customer-display/data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(displayData)
                }).catch(err => console.error('Failed to sync to server:', err));
            }
        },
        
        clearCustomerDisplay() {
            const emptyData = {
                cartItems: [], subtotal: 0, tax: 0, discount: 0, total: 0,
                taxRate: 0.10, orderType: '', tableNumber: '',
                customerName: '', mode: '', orderNumber: ''
            };
            localStorage.removeItem('pos_customer_display');
            if (this.broadcastChannel) {
                try { this.broadcastChannel.postMessage(emptyData); } catch(e) {}
            }
            if (this.displayMode === 'network') {
                fetch('/api/customer-display/data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(emptyData)
                }).catch(err => console.error('Failed to clear server:', err));
            }
        },
        
        calculateChange() {
            this.change = (this.amountPaid || 0) - this.orderTotal;
        },
        
        setAmount(amount) {
            this.amountPaid = amount;
            this.amountPaidFormatted = this.formatInputMoney(amount);
            this.calculateChange();
        },
        
        handleAmountInput(value) {
            // Remove all non-digit characters
            const numericValue = value.replace(/\D/g, '');
            
            // Update the actual numeric value
            this.amountPaid = numericValue ? parseInt(numericValue) : 0;
            
            // Update the formatted display
            this.amountPaidFormatted = this.formatInputMoney(this.amountPaid);
            
            this.calculateChange();
        },
        
        formatInputMoney(amount) {
            if (!amount || amount === 0) return '';
            return new Intl.NumberFormat('id-ID').format(amount);
        },

        appendNumber(num) {
            let currentStr = this.amountPaid.toString();
            if (currentStr === '0') currentStr = '';
            
            // Limit max digits
            if (currentStr.length < 12) {
                let newStr = currentStr + num;
                this.handleAmountInput(newStr);
            }
        },

        deleteNumber() {
            let currentStr = this.amountPaid.toString();
            if (currentStr.length > 1) {
                let newStr = currentStr.slice(0, -1);
                this.handleAmountInput(newStr);
            } else {
                this.handleAmountInput('0');
            }
        },

        clearNumber() {
            this.handleAmountInput('0');
        },

        async processPayment() {
            // If QRIS is selected and Midtrans is configured, use Midtrans Snap
            if (this.paymentMethod === 'qris' && this.midtransConfigured) {
                await this.processMidtransPayment();
                return;
            }
            
            if (this.change < 0) {
                alert('Payment amount is insufficient');
                return;
            }
            
            this.processing = true;
            
            try {
                const response = await fetch('/orders/{{ $order->id }}/payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        payment_method: this.paymentMethod,
                        amount_paid: this.amountPaid
                    })
                });
                
                const result = await response.json();
                
                console.log('Payment response:', result);
                
                if (response.ok && result.success) {
                    // Clear customer display
                    this.clearCustomerDisplay();
                    
                    // Set payment completed and stop processing
                    this.paymentCompleted = true;
                    this.processing = false;
                } else {
                    console.error('Payment error:', result);
                    alert('Error: ' + (result.message || 'Failed to process payment'));
                    this.processing = false;
                }
            } catch (error) {
                console.error('Payment exception:', error);
                alert('Failed to process payment: ' + error.message);
                this.processing = false;
            }
        },
        
        async processMidtransPayment() {
            this.processing = true;
            
            try {
                console.log('Processing Midtrans payment...');
                
                const response = await fetch('/orders/{{ $order->id }}/payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        payment_method: 'qris',
                        amount_paid: this.orderTotal
                    })
                });
                
                const result = await response.json();
                console.log('Payment response:', result);
                
                if (result.success && result.use_midtrans && result.snap_token) {
                    console.log('Opening Midtrans Snap with token:', result.snap_token.substring(0, 20) + '...');
                    
                    // Check if snap is available
                    if (typeof window.snap === 'undefined') {
                        alert('Midtrans Snap is not loaded. Please refresh the page and try again.');
                        this.processing = false;
                        return;
                    }
                    
                    // Open Midtrans Snap
                    window.snap.pay(result.snap_token, {
                        onSuccess: (result) => {
                            console.log('Midtrans success:', result);
                            this.clearCustomerDisplay();
                            this.paymentCompleted = true;
                            this.processing = false;
                        },
                        onPending: (result) => {
                            console.log('Midtrans pending:', result);
                            this.clearCustomerDisplay();
                            this.paymentCompleted = true;
                            this.processing = false;
                        },
                        onError: (result) => {
                            console.log('Midtrans error:', result);
                            alert('Payment failed: ' + (result.status_message || 'Unknown error'));
                            this.processing = false;
                        },
                        onClose: () => {
                            console.log('Midtrans popup closed');
                            this.processing = false;
                        }
                    });
                } else {
                    alert('Error: ' + (result.message || 'Failed to generate payment'));
                    this.processing = false;
                }
            } catch (error) {
                console.error('Midtrans exception:', error);
                alert('Failed to process payment: ' + error.message);
                this.processing = false;
            }
        },
        
        printBill() {
            // Clear customer display before printing
            this.clearCustomerDisplay();
            
            // Load receipt in hidden iframe and trigger print dialog without leaving page
            const receiptUrl = '/orders/{{ $order->id }}/receipt';
            
            let iframe = document.getElementById('print-iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'print-iframe';
                iframe.style.position = 'absolute';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = 'none';
                iframe.style.overflow = 'hidden';
                document.body.appendChild(iframe);
            }
            
            iframe.onload = function() {
                setTimeout(() => {
                    iframe.contentWindow.print();
                    // print() blocks until dialog closes, then redirect
                    window.location.href = '{{ route("pos.index") }}';
                }, 500);
            };
            
            iframe.src = receiptUrl;
        },
        
        formatMoney(amount) {
            return new Intl.NumberFormat('id-ID').format(Math.abs(amount));
        }
    }
}
</script>

@if($midtransConfigured)
<!-- Midtrans Snap JS - Use sandbox for testing -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>
<script>
    // Verify Snap is loaded
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.snap !== 'undefined') {
            console.log('Midtrans Snap loaded successfully');
        } else {
            console.error('Midtrans Snap failed to load');
        }
    });
</script>
@endif
@endpush
@endsection
