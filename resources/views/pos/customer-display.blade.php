<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Display - {{ $restaurantName }}</title>
    
    @vite(['resources/css/app.css'])
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
            padding: 0;
            margin: 0;
            display: flex;
            height: 100vh;
        }
        .poster-container {
            flex: 1;
            background: #2d3748;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .poster-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .poster-placeholder {
            color: #a0aec0;
            text-align: center;
            padding: 2rem;
        }
        .receipt-container {
            background: white;
            width: 50%;
            flex-shrink: 0;
            box-shadow: -10px 0 30px rgba(0,0,0,0.2);
            font-family: 'Courier New', monospace;
            line-height: 1.8;
            overflow-y: auto;
        }
        .item-price {
            padding-right: 1rem;
        }
        .dashed-line {
            border-top: 4px dashed #ccc;
            margin: 40px 0;
        }
        .item-enter {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
    </style>
</head>
<body x-data="customerDisplay()" x-init="init()">
    <!-- Poster/Ad Area (Left Side) -->
    <div class="poster-container">
        <template x-if="posterImage">
            <img :src="posterImage" alt="Advertisement" />
        </template>
        <template x-if="!posterImage">
            <div class="poster-placeholder">
                <svg class="w-32 h-32 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-2xl font-semibold">Advertisement Space</p>
                <p class="text-lg mt-2">Upload poster in settings</p>
            </div>
        </template>
    </div>

    <!-- Customer Display (Right Side) -->
    <div class="receipt-container flex flex-col">
        
        <!-- Receipt Header -->
        <div class="text-center py-14 px-20 border-b-4 border-gray-800 bg-gradient-to-b from-gray-50 to-white" style="margin: 40px; margin-bottom: 0;">
            @if($restaurantLogo)
            <img src="{{ asset('storage/' . $restaurantLogo) }}" alt="{{ $restaurantName }}" class="h-28 w-28 object-contain mx-auto mb-8">
            @endif
            <h1 class="text-3xl font-bold text-gray-900 uppercase tracking-wide mb-4">{{ $restaurantName }}</h1>
            <p class="text-base text-gray-600 font-medium">Customer Display</p>
            
            <!-- Payment Mode Badge -->
            <template x-if="mode === 'payment'">
                <div class="mt-6 inline-block bg-gradient-to-r from-green-500 to-green-600 text-white px-8 py-3 text-base font-bold uppercase tracking-wider rounded-xl shadow-lg">
                    Processing Payment
                </div>
            </template>
            
            <!-- Order Info -->
            <template x-if="orderType || orderNumber">
                <div class="mt-6 text-sm text-gray-700">
                    <template x-if="mode === 'payment' && orderNumber">
                        <div class="font-bold text-xl mb-4">ORDER #<span x-text="orderNumber"></span></div>
                    </template>
                    <div class="flex items-center justify-center gap-6 flex-wrap text-base">
                        <template x-if="orderType">
                            <span class="font-semibold" x-text="orderType === 'dine_in' ? '🍽️ Dine In' : '🛍️ Take Away'"></span>
                        </template>
                        <template x-if="orderType === 'dine_in' && tableNumber">
                            <span>| Table <span class="font-bold" x-text="tableNumber"></span></span>
                        </template>
                        <template x-if="customerName">
                            <span>| <span x-text="customerName"></span></span>
                        </template>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- Receipt Body -->
        <div class="flex-1 overflow-y-auto px-20 py-12" style="margin: 0 40px;" x-ref="cartContainer">
            <template x-if="cartItems.length === 0">
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <div class="pulse mb-6">
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <p class="text-lg font-semibold mb-3">Waiting for Order...</p>
                    <p class="text-sm">Items will appear here</p>
                </div>
            </template>
            
            <template x-if="cartItems.length > 0">
                <div>
                    <div class="text-center text-lg font-bold text-gray-500 uppercase tracking-wide mb-6 pb-3 border-b-2 border-gray-300">ORDER ITEMS</div>
                    <template x-for="(item, index) in cartItems" :key="index">
                        <div class="item-enter mb-6 pb-5 border-b-2 border-gray-300">
                            <div class="flex justify-between items-start gap-8">
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-gray-900 text-xl mb-3 leading-relaxed" x-text="item.name"></div>
                                    <div class="text-sm text-gray-600 mt-2">
                                        <span x-text="item.quantity"></span> x Rp <span x-text="formatMoney(item.price)"></span>
                                    </div>
                                </div>
                                <div class="font-bold text-gray-900 text-right text-xl flex-shrink-0 ml-6">
                                    Rp <span x-text="formatMoney(item.price * item.quantity)"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
        
        <!-- Receipt Footer / Totals -->
        <template x-if="cartItems.length > 0">
            <div class="border-t-4 border-gray-800 px-20 py-12 bg-gradient-to-b from-white to-gray-50" style="margin: 0 40px 40px 40px;">
                <div class="space-y-4 text-base mb-6 px-4">
                    <div class="flex justify-between">
                        <span class="text-gray-700">Subtotal</span>
                        <span class="font-mono">Rp <span x-text="formatMoney(subtotal)"></span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">
                            Tax (<span x-text="(taxRate * 100)"></span>%)
                            <span x-show="taxType === 'include'" class="text-sm">(incl)</span>
                        </span>
                        <span class="font-mono">Rp <span x-text="formatMoney(tax)"></span></span>
                    </div>
                    <template x-if="discount > 0">
                        <div class="flex justify-between text-green-700">
                            <span>Discount</span>
                            <span class="font-mono">- Rp <span x-text="formatMoney(discount)"></span></span>
                        </div>
                    </template>
                </div>
                <div class="dashed-line"></div>
                <div class="flex justify-between items-center text-2xl font-bold my-4 p-5 bg-yellow-50 rounded-xl border-2 border-yellow-300">
                    <span class="text-gray-900 uppercase">TOTAL</span>
                    <span class="font-mono text-3xl text-green-700">Rp <span x-text="formatMoney(total)"></span></span>
                </div>
                <div class="dashed-line"></div>
                <div class="text-center text-lg text-gray-500 mt-6 font-semibold">
                    Thank You!
                </div>
            </div>
        </template>
    </div>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
    function customerDisplay() {
        return {
            cartItems: [],
            subtotal: 0,
            tax: 0,
            discount: 0,
            total: 0,
            taxRate: 0.10,
            taxType: 'exclude',
            orderType: '',
            tableNumber: '',
            customerName: '',
            mode: '',
            orderNumber: '',
            posterImage: '{{ $posterImage ?? '' }}',
            displayMode: '{{ $displayMode ?? 'local' }}',
            broadcastChannel: null,
            
            init() {
                // Load initial data from localStorage
                this.loadDataFromLocalStorage();
                
                // BroadcastChannel for instant same-browser updates (local mode primary)
                if (typeof BroadcastChannel !== 'undefined') {
                    try {
                        this.broadcastChannel = new BroadcastChannel('pos_customer_display');
                        this.broadcastChannel.onmessage = (e) => {
                            this.updateDisplayData(e.data);
                        };
                    } catch(e) {
                        console.warn('BroadcastChannel not available:', e);
                    }
                }
                
                // Listen for localStorage changes (fallback for same device)
                window.addEventListener('storage', (e) => {
                    if (e.key === 'pos_customer_display') {
                        if (e.newValue === null) {
                            // Item was removed — clear display
                            this.updateDisplayData({
                                cartItems: [], subtotal: 0, tax: 0, discount: 0, total: 0,
                                taxRate: 0.10, orderType: '', tableNumber: '',
                                customerName: '', mode: '', orderNumber: ''
                            });
                        } else {
                            this.loadDataFromLocalStorage();
                        }
                    }
                });
                
                // Poll server only in network mode (for cross-device updates)
                if (this.displayMode === 'network') {
                    this.loadDataFromServer();
                    setInterval(() => {
                        this.loadDataFromServer();
                    }, 2000);
                }
            },
            
            loadDataFromLocalStorage() {
                try {
                    const data = localStorage.getItem('pos_customer_display');
                    if (data) {
                        const parsed = JSON.parse(data);
                        this.updateDisplayData(parsed);
                    }
                } catch (e) {
                    console.error('Error loading from localStorage:', e);
                }
            },
            
            loadDataFromServer() {
                // Fetch data from server for cross-device support
                fetch('/api/customer-display/data')
                    .then(response => response.json())
                    .then(data => {
                        this.updateDisplayData(data);
                    })
                    .catch(err => {
                        console.error('Failed to load from server:', err);
                    });
            },
            
            updateDisplayData(data) {
                const prevCount = this.cartItems.length;
                this.cartItems = data.cartItems || [];
                this.subtotal = data.subtotal || 0;
                this.tax = data.tax || 0;
                this.discount = data.discount || 0;
                this.total = data.total || 0;
                this.taxRate = data.taxRate || 0.10;
                this.taxType = data.taxType || 'exclude';
                this.orderType = data.orderType || '';
                this.tableNumber = data.tableNumber || '';
                this.customerName = data.customerName || '';
                this.mode = data.mode || '';
                this.orderNumber = data.orderNumber || '';
                // Auto-scroll ke item terakhir jika ada item baru
                if (this.cartItems.length > prevCount) {
                    this.$nextTick(() => { this.$refs.cartContainer.scrollTop = this.$refs.cartContainer.scrollHeight; });
                }
            },
            
            formatMoney(amount) {
                return new Intl.NumberFormat('id-ID').format(amount);
            }
        }
    }
    </script>
</body>
</html>
