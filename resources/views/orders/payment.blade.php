@extends('layouts.app')

@section('title', 'Payment - Order #' . $order->order_number)
@section('header', __('payment.title'))

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
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('payment.order_summary') }}</h2>
            
            <div class="mb-4 pb-4 border-b">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">{{ __('payment.bill_number') }}</span>
                    <span class="font-medium">{{ $order->bill_number }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">{{ __('payment.order_number') }}</span>
                    <span class="font-medium">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">{{ __('payment.type') }}</span>
                    <span class="font-medium capitalize">{{ str_replace('_', ' ', $order->type->value) }}</span>
                </div>
                @if($order->table)
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">{{ __('payment.table') }}</span>
                    <span class="font-medium">{{ __('payment.table') }} {{ $order->table->number }}</span>
                </div>
                @endif
                @if($order->customer_name)
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">{{ __('payment.customer') }}</span>
                    <span class="font-medium">{{ $order->customer_name }}</span>
                </div>
                @endif
            </div>
            
            <div class="space-y-3 mb-4">
                <h3 class="font-semibold text-gray-900">{{ __('payment.items') }}</h3>
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
                    <span class="text-gray-600">{{ __('payment.subtotal') }}</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    @php $taxType = \App\Models\Setting::get('tax_type', 'exclude'); @endphp
                    <span class="text-gray-600">{{ __('payment.tax') }} ({{ (int)$order->tax }}%){{ $taxType === 'include' ? ' (incl)' : '' }}</span>
                    <span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                </div>
                @if($order->discount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>{{ __('payment.discount') }}</span>
                    <span>- Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-lg font-bold border-t pt-2">
                    <span>{{ __('payment.total') }}</span>
                    <span class="text-primary-600">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        
        <!-- Payment Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('payment.payment_method') }}</h2>
            
            <form @submit.prevent="processPayment()">
                <!-- Payment Methods Toggle -->
                <div class="mb-4 flex items-center bg-blue-50 p-3 rounded-lg border border-blue-100">
                    <label class="cursor-pointer flex items-center w-full">
                        <input type="checkbox" x-model="isSplitPayment" class="form-checkbox text-primary-600 w-5 h-5 rounded">
                        <span class="ml-3 font-semibold text-blue-900">Split Payment</span>
                    </label>
                </div>

                <!-- Single Payment View -->
                <div x-show="!isSplitPayment">
                    <!-- Payment Methods -->
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <label class="relative cursor-pointer">
                            <input type="radio" x-model="paymentMethod" value="cash" class="sr-only">
                            <div :class="paymentMethod === 'cash' ? 'border-primary-600 bg-primary-50' : 'border-gray-200'" class="border-2 rounded-lg p-4 text-center transition">
                                <i class="fas fa-money-bill-wave text-3xl mb-2" :class="paymentMethod === 'cash' ? 'text-primary-600' : 'text-gray-400'"></i>
                                <p class="font-medium text-sm">Cash</p>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" x-model="paymentMethod" value="qris" class="sr-only">
                            <div :class="paymentMethod === 'qris' ? 'border-primary-600 bg-primary-50' : 'border-gray-200'" class="border-2 rounded-lg p-4 text-center transition">
                                <i class="fas fa-qrcode text-3xl mb-2" :class="paymentMethod === 'qris' ? 'text-primary-600' : 'text-gray-400'"></i>
                                <p class="font-medium text-sm">QRIS</p>
                            </div>
                        </label>
                    </div>

                    <!-- Amount Paid -->
                    <div class="mb-6 relative" @click.outside="if(!event.target.closest('.keypad-container')) showKeypad = false">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('payment.amount_paid') }}</label>
                        <div class="relative">
                            <input type="text" inputmode="none" x-model="amountPaidFormatted" @input="handleAmountInput($event.target.value)" @focus="showKeypad = true" class="input text-lg font-bold w-full" placeholder="0" required>
                            <button type="button" @click="showKeypad = !showKeypad" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"><i class="fas fa-keyboard text-xl"></i></button>
                        </div>
                        <div class="grid grid-cols-4 gap-2 mt-3">
                            <button type="button" @click="setAmount({{ $order->total }})" class="btn-secondary text-xs py-2">{{ __('payment.exact') }}</button>
                            <button type="button" @click="setAmount(50000)" class="btn-secondary text-xs py-2">50k</button>
                            <button type="button" @click="setAmount(100000)" class="btn-secondary text-xs py-2">100k</button>
                            <button type="button" @click="setAmount(200000)" class="btn-secondary text-xs py-2">200k</button>
                        </div>
                        <div x-show="showKeypad" x-transition class="keypad-container mt-3 bg-gray-50 border border-gray-200 rounded-xl p-3 grid grid-cols-3 gap-2">
                             <template x-for="n in [1,2,3,4,5,6,7,8,9]" :key="n">
                                 <button type="button" @click="appendNumber(n)" class="bg-white hover:bg-gray-100 text-gray-800 font-bold text-3xl py-5 rounded-lg shadow-sm border border-gray-200" x-text="n"></button>
                             </template>
                             <button type="button" @click="appendNumber('00')" class="bg-white hover:bg-gray-100 text-gray-800 font-bold text-3xl py-5 rounded-lg shadow-sm border border-gray-200">00</button>
                             <button type="button" @click="appendNumber(0)" class="bg-white hover:bg-gray-100 text-gray-800 font-bold text-3xl py-5 rounded-lg shadow-sm border border-gray-200">0</button>
                             <button type="button" @click="deleteNumber()" class="bg-red-50 hover:bg-red-100 text-red-600 font-bold text-3xl py-5 rounded-lg shadow-sm border border-red-200"><i class="fas fa-backspace text-3xl"></i></button>
                             <button type="button" @click="clearNumber()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-2xl py-4 rounded-lg shadow-sm border border-gray-300 col-span-1">{{ __('payment.clear') }}</button>
                             <button type="button" @click="showKeypad = false" class="bg-primary-600 hover:bg-primary-700 text-white font-bold text-2xl py-4 rounded-lg shadow-sm col-span-2">{{ __('payment.done') }}</button>
                        </div>
                    </div>
                </div>

                <!-- Split Payment View -->
                <div x-show="isSplitPayment" x-cloak class="mb-6 space-y-3" @click.outside="if(!event.target.closest('.split-keypad-container')) activeSplitIndex = null">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Split Payments (QRIS / Tunai)</label>
                    <template x-for="(payment, index) in multiPayments" :key="index">
                        <div class="flex gap-2 items-center bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                                          <select x-model="payment.method" disabled class="border-gray-300 rounded p-2 text-sm w-1/3 text-gray-700 font-semibold bg-gray-100 cursor-not-allowed">
                                  <option value="cash">Cash (Tunai)</option>
                                  <option value="qris">QRIS</option>
                              </select>
                            <div class="relative w-full">
                                <span class="absolute left-3 top-2.5 text-gray-500 text-sm">Rp</span>
                                <input type="text" inputmode="none"
                                       x-model="payment.formatted_amount"
                                       @input="handleSplitAmountInput(index, $event.target.value)"
                                       @focus="activeSplitIndex = index; $event.target.select()"
                                       class="border-gray-300 rounded p-2 pl-8 text-sm font-bold text-right w-full focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="0">
                            </div>

                        </div>
                    </template>

                    <!-- Split Keypad -->
                    <div x-show="activeSplitIndex !== null" x-transition class="split-keypad-container mt-3 bg-gray-50 border border-gray-200 rounded-xl p-3 grid grid-cols-3 gap-2">
                         <template x-for="n in [1,2,3,4,5,6,7,8,9]" :key="n">
                             <button type="button" @click="appendSplitNumber(n)" class="bg-white hover:bg-gray-100 text-gray-800 font-bold text-3xl py-5 rounded-lg shadow-sm border border-gray-200" x-text="n"></button>
                         </template>
                         <button type="button" @click="appendSplitNumber('00')" class="bg-white hover:bg-gray-100 text-gray-800 font-bold text-3xl py-5 rounded-lg shadow-sm border border-gray-200">00</button>
                         <button type="button" @click="appendSplitNumber(0)" class="bg-white hover:bg-gray-100 text-gray-800 font-bold text-3xl py-5 rounded-lg shadow-sm border border-gray-200">0</button>
                         <button type="button" @click="deleteSplitNumber()" class="bg-red-50 hover:bg-red-100 text-red-600 font-bold text-3xl py-5 rounded-lg shadow-sm border border-red-200"><i class="fas fa-backspace text-3xl"></i></button>
                         <button type="button" @click="clearSplitNumber()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-2xl py-4 rounded-lg shadow-sm border border-gray-300 col-span-1">{{ __('payment.clear') }}</button>
                         <button type="button" @click="activeSplitIndex = null" class="bg-primary-600 hover:bg-primary-700 text-white font-bold text-2xl py-4 rounded-lg shadow-sm col-span-2">{{ __('payment.done') }}</button>
                    </div>
                  </div>

                  <!-- Change -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-100 shadow-inner">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-600 font-medium text-sm">Total Paid</span>
                        <span class="text-lg font-bold text-gray-800" x-text="`Rp ${formatMoney(totalCollected)}`"></span>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-200 mt-2 pt-2">
                        <span class="text-gray-700 font-medium">{{ __('payment.change') }}</span>
                        <span class="text-2xl font-bold" :class="change >= 0 ? 'text-green-600' : 'text-red-600'" x-text="`Rp ${formatMoney(change)}`"></span>
                    </div>
                    <template x-if="change < 0">
                        <p class="text-red-600 text-xs mt-2 font-medium">{{ __('payment.insufficient_amount') }}</p>
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
                            <p class="text-green-800 font-bold text-lg">{{ __('payment.payment_successful') }}</p>
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
        
        isSplitPayment: false,
        multiPayments: [{ method: 'qris', amount: 0, formatted_amount: '0' }, { method: 'cash', amount: 0, formatted_amount: '0' }],
        activeSplitIndex: null,
        
        change: 0,
        showKeypad: false,
        processing: false,
        paymentCompleted: false,
        midtransConfigured: {{ $midtransConfigured ? 'true' : 'false' }},
        displayMode: '{{ $displayMode ?? 'local' }}',
        broadcastChannel: null,

        get totalCollected() {
            if (this.isSplitPayment) {
                return this.multiPayments.reduce((sum, p) => sum + (parseInt(p.amount) || 0), 0);
            }
            return (this.amountPaid || 0);
        },

        init() {
            if (typeof BroadcastChannel !== 'undefined') {
                try { this.broadcastChannel = new BroadcastChannel('pos_customer_display'); } catch(e) {}
            }
            this.syncToCustomerDisplay();
            this.calculateChange();
            
            // Watch for changes
            this.$watch('isSplitPayment', value => {
                this.calculateChange();
            });
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
            this.change = this.totalCollected - this.orderTotal;
        },
        
        addSplitPayment() {
            this.multiPayments.push({ method: 'cash', amount: 0, formatted_amount: '' });
            this.calculateChange();
        },
        
        removePayment(index) {
            this.multiPayments.splice(index, 1);
            this.calculateChange();
        },
        
        handleSplitAmountInput(index, value) {
            const numericValue = value.replace(/\D/g, '');
            const amount = numericValue ? parseInt(numericValue) : 0;
            this.multiPayments[index].amount = amount;
            this.multiPayments[index].formatted_amount = this.formatInputMoney(amount);
            this.calculateChange();
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

        appendSplitNumber(num) {
            if (this.activeSplitIndex === null) return;
            let currentStr = this.multiPayments[this.activeSplitIndex].amount.toString();
            if (currentStr === '0') currentStr = '';
            
            if (currentStr.length < 12) {
                let newStr = currentStr + num;
                this.handleSplitAmountInput(this.activeSplitIndex, newStr);
            }
        },

        deleteSplitNumber() {
            if (this.activeSplitIndex === null) return;
            let currentStr = this.multiPayments[this.activeSplitIndex].amount.toString();
            if (currentStr.length > 1) {
                let newStr = currentStr.slice(0, -1);
                this.handleSplitAmountInput(this.activeSplitIndex, newStr);
            } else {
                this.handleSplitAmountInput(this.activeSplitIndex, '0');
            }
        },

        clearSplitNumber() {
            if (this.activeSplitIndex === null) return;
            this.handleSplitAmountInput(this.activeSplitIndex, '0');
        },

        setSplitAmount(amount) {
            if (this.activeSplitIndex === null) return;
            this.handleSplitAmountInput(this.activeSplitIndex, amount.toString());
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
                        payments: this.isSplitPayment ? this.multiPayments.map(p => ({method: p.method, amount: p.amount})) : [{method: this.paymentMethod, amount: this.amountPaid}]
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
                        payments: [{method: 'qris', amount: this.orderTotal}]
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













