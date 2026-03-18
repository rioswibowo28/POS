@extends('layouts.app')

@section('title', 'POS / Kasir')
@section('header', 'POS / Kasir')

@section('content')
<div x-data="posApp()" x-init="init()" class="h-full">
    
    <!-- Order Limit Warning Banner -->
    <template x-if="isOrderLimitActive() && orderLimitAmount > 0 && todayOrderTotal >= orderLimitAmount">
        <div class="bg-amber-50 border border-amber-300 rounded-lg p-3 mb-3 flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-amber-500 text-lg"></i>
            <div class="text-sm text-amber-800">
                <span class="font-semibold">Peringatan Limit Penjualan!</span>
                Total hari ini: <strong x-text="formatRupiah(todayOrderTotal)"></strong> / <strong x-text="formatRupiah(orderLimitAmount)"></strong>
                <span class="text-amber-600">(berlaku <span x-text="orderLimitStart"></span> - <span x-text="orderLimitEnd"></span>)</span>
            </div>
        </div>
    </template>

    <!-- Step 1: Table Selection (for Dine In) -->
    <div x-show="step === 'table' && orderType === 'dine_in'" class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Select Table</h3>
                <button @click="orderType = 'take_away'; step = 'menu'" class="btn-secondary text-sm">
                    <i class="fas fa-shopping-bag mr-2"></i> Switch to Takeaway
                </button>
            </div>
            
            <div class="w-full">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 gap-4 max-w-full">
                    @foreach($tables as $table)
                    <div @click="handleTableClick({{ $table->id }}, '{{ $table->number }}', '{{ $table->status->value }}', {{ $table->currentOrder ? $table->currentOrder->id : 'null' }})"
                         class="relative w-full border-2 overflow-hidden {{ $table->status->value === 'available' ? 'border-green-500 bg-white' : 'border-red-500 bg-white' }} rounded-xl transition-all duration-200 cursor-pointer shadow-md hover:shadow-xl hover:scale-105"
                         style="aspect-ratio: 4 / 3; max-height: 18vh;">

                        <!-- Status Bar -->
                        <div class="absolute top-0 left-0 right-0 {{ $table->status->value === 'available' ? 'bg-green-500' : 'bg-red-500' }} text-white text-xs font-bold py-1 px-2 text-center">
                            @if($table->status->value === 'available')
                                <i class="fas fa-check-circle mr-1"></i>Available
                            @else
                                <i class="fas fa-clock mr-1"></i>In Use
                            @endif
                        </div>
                        
                        <!-- Content -->
                        <div class="flex flex-col items-center justify-center h-full pt-6 pb-2">
                            <i class="fas fa-chair text-3xl mb-2 {{ $table->status->value === 'available' ? 'text-green-600' : 'text-red-600' }}"></i>
                            <span class="font-bold text-lg text-gray-800">{{ $table->number }}</span>
                            <span class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-users text-xs mr-1"></i>{{ $table->capacity }} orang
                            </span>
                        </div>
                        
                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 {{ $table->status->value === 'available' ? 'bg-green-500' : 'bg-red-500' }} opacity-0 hover:opacity-10 transition-opacity duration-200"></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Step 2: Menu Selection -->
    <div x-show="step === 'menu'" class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
        
        <!-- Left Side - Products -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm h-full flex flex-col">
                
                <!-- Search & Categories -->
                <div class="p-4 border-b">
                    <!-- Search -->
                      <div class="relative mb-4" @click.outside="if(!event.target.closest('.keyboard-container')) showKeyboard = false">
                          <!-- Wrap input and icon together so inset-y-0 only measures the input height -->
                            <div class="relative mb-3">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text"
                                       x-model="searchQuery"
                                       @input="filterProducts()"
                                       @focus="showKeyboard = true"
                                       inputmode="none"
                                       class="input w-full"
                                       style="padding-left: 2.5rem;"
                                         placeholder="Search products...">
                              </div>
                          <!-- On-Screen Keyboard -->
                          <div x-show="showKeyboard" x-transition class="keyboard-container absolute z-50 bg-gray-100 border border-gray-300 rounded-xl shadow-2xl p-3" style="top: 100%; left: 0; right: 0; margin-top: 5px;">
                              <!-- Row 1 -->
                              <div class="flex justify-center gap-1 mb-2">
                                  <template x-for="key in ['q','w','e','r','t','y','u','i','o','p']">
                                      <button @click.prevent="typeChar(key)" class="w-10 h-12 bg-white rounded shadow text-lg font-medium hover:bg-gray-200 uppercase" x-text="key"></button>
                                  </template>
                              </div>
                              <!-- Row 2 -->
                              <div class="flex justify-center gap-1 mb-2">
                                  <template x-for="key in ['a','s','d','f','g','h','j','k','l']">
                                      <button @click.prevent="typeChar(key)" class="w-10 h-12 bg-white rounded shadow text-lg font-medium hover:bg-gray-200 uppercase" x-text="key"></button>
                                  </template>
                              </div>
                              <!-- Row 3 -->
                              <div class="flex justify-center gap-1 mb-2">
                                  <template x-for="key in ['z','x','c','v','b','n','m']">
                                      <button @click.prevent="typeChar(key)" class="w-10 h-12 bg-white rounded shadow text-lg font-medium hover:bg-gray-200 uppercase" x-text="key"></button>
                                  </template>
                              </div>
                              <!-- Row 4 -->
                              <div class="flex justify-center gap-2">
                                  <button @click.prevent="clearSearch()" class="px-4 h-12 bg-red-100 text-red-600 rounded shadow font-medium hover:bg-red-200">Clear</button>
                                  <button @click.prevent="typeChar(' ')" class="flex-1 h-12 bg-white rounded shadow hover:bg-gray-200"></button>
                                  <button @click.prevent="deleteChar()" class="px-4 h-12 bg-gray-200 rounded shadow hover:bg-gray-300"><i class="fas fa-backspace"></i></button>
                                  <button @click.prevent="showKeyboard = false" class="px-4 h-12 bg-primary-600 text-white rounded shadow font-medium hover:bg-primary-700">Done</button>
                              </div>
                          </div>
                      </div>
                    
                    <!-- Categories -->
                    <div class="flex gap-2 overflow-x-auto pb-2">
                        <button @click="selectedCategory = 'favorite'; filterProducts()"
                                :class="selectedCategory === 'favorite' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700'"
                                class="px-4 py-2 rounded-lg whitespace-nowrap text-sm font-medium transition">
                            ⭐ Favorit
                        </button>
                        <button @click="selectedCategory = null; filterProducts()"
                                :class="selectedCategory === null ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700'"
                                class="px-4 py-2 rounded-lg whitespace-nowrap text-sm font-medium transition">
                            All
                        </button>
                        <button @click="selectedCategory = 'package'; filterProducts()"
                                :class="selectedCategory === 'package' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700'"
                                class="px-4 py-2 rounded-lg whitespace-nowrap text-sm font-medium transition">
                            📦 Paket (<span x-text="packages.length"></span>)
                        </button>
                        @foreach($categories as $category)
                        <button @click="selectedCategory = {{ $category->id }}; filterProducts()"
                                :class="selectedCategory === {{ $category->id }} ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700'"
                                class="px-4 py-2 rounded-lg whitespace-nowrap text-sm font-medium transition">
                            {{ $category->name }} ({{ $category->products_count }})
                        </button>
                        @endforeach
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="flex-1 overflow-y-auto p-4">
                    <!-- Package Cards (shown when Paket tab selected) -->
                    <div x-show="selectedCategory === 'package'" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <template x-for="pkg in filteredPackages" :key="'pkg-'+pkg.id">
                            <div @click="addPackageToCart(pkg)" 
                                 class="bg-gradient-to-br from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-lg p-4 cursor-pointer hover:border-indigo-500 hover:shadow-md transition relative">
                                <!-- Savings badge -->
                                <template x-if="pkg.savings > 0">
                                    <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow">
                                        <span x-text="'Hemat ' + formatMoney(pkg.savings)"></span>
                                    </div>
                                </template>

                                <div class="aspect-square bg-gradient-to-br from-indigo-400 to-purple-500 rounded-lg mb-3 flex items-center justify-center">
                                    <template x-if="pkg.image">
                                        <img :src="`/storage/${pkg.image}`" :alt="pkg.name" class="w-full h-full object-cover rounded-lg">
                                    </template>
                                    <template x-if="!pkg.image">
                                        <i class="fas fa-cubes text-4xl text-white"></i>
                                    </template>
                                </div>
                                <h4 class="font-semibold text-gray-900 text-sm mb-1" x-text="pkg.name"></h4>
                                <p class="text-indigo-600 font-bold" x-text="`Rp ${formatMoney(pkg.price)}`"></p>
                                <template x-if="pkg.savings > 0">
                                    <p class="text-xs text-gray-400 line-through" x-text="`Rp ${formatMoney(pkg.normal_price)}`"></p>
                                </template>
                                <div class="mt-2 space-y-0.5">
                                    <template x-for="item in pkg.items" :key="item.id">
                                        <p class="text-xs text-gray-500 truncate">
                                            <i class="fas fa-check text-green-400 mr-1"></i>
                                            <span x-text="item.product ? (item.quantity > 1 ? item.quantity + 'x ' : '') + item.product.name : 'N/A'"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template x-if="filteredPackages.length === 0">
                            <div class="col-span-full text-center py-12 text-gray-400">
                                <i class="fas fa-cubes text-5xl mb-3"></i>
                                <p>Belum ada paket tersedia</p>
                            </div>
                        </template>
                    </div>

                    <!-- Product Cards (shown for all other tabs) -->
                    <div x-show="selectedCategory !== 'package'" class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div @click="addToCart(product)" 
                                 class="bg-white border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-primary-500 hover:shadow-md transition">
                                <div class="aspect-square bg-gray-100 rounded-lg mb-3 flex items-center justify-center">
                                    <template x-if="product.image">
                                        <img :src="`/storage/${product.image}`" :alt="product.name" class="w-full h-full object-cover rounded-lg">
                                    </template>
                                    <template x-if="!product.image">
                                        <i class="fas fa-utensils text-4xl text-gray-300"></i>
                                    </template>
                                </div>
                                <h4 class="font-semibold text-gray-900 text-sm mb-1" x-text="product.name"></h4>
                                <p class="text-primary-600 font-bold" x-text="`Rp ${formatMoney(product.price)}`"></p>
                                <template x-if="product.inventory && product.inventory.quantity <= product.inventory.min_quantity">
                                    <span class="text-xs text-red-600">Stock Low</span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Cart & Checkout -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm flex flex-col" style="height: calc(100vh - 8rem);">
                
                <!-- Order Info Header -->
                <div class="p-4 border-b bg-primary-50 flex-shrink-0">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-gray-900">Order Details</h3>
                        <div class="flex gap-2">
                            <button @click="openCustomerDisplay()" class="text-blue-600 hover:text-blue-700 text-sm" title="Open Customer Display">
                                <i class="fas fa-tv mr-1"></i> Display
                            </button>
                            <button @click="backToTableSelection()" class="text-primary-600 hover:text-primary-700 text-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Change
                            </button>
                        </div>
                    </div>
                    
                    <!-- No Tax Mode Checkbox -->
                    @if (\App\Models\Setting::get('pos_show_tax_flag', '1') == '1')
                    <div class="mb-3">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox"
                                   x-model="flag"
                                   @change="calculateTotals()"
                                   class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 focus:ring-2">
                            </label>
                    </div>
                    @endif
                    <template x-if="orderType === 'dine_in'">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-chair text-primary-600"></i>
                            <span class="font-medium text-gray-900">Table <span x-text="selectedTableNumber"></span></span>
                        </div>
                    </template>
                    
                    <template x-if="orderType === 'take_away'">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-shopping-bag text-primary-600"></i>
                                <span class="font-medium text-gray-900">Takeaway</span>
                            </div>
                            <input type="text" x-model="customerName" @input="syncToCustomerDisplay()" class="input text-sm" placeholder="Customer name (optional)">
                            <input type="text" x-model="customerPhone" class="input text-sm" placeholder="Phone number (optional)">
                        </div>
                    </template>
                    
                    <template x-if="orderType === 'delivery'">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-motorcycle text-primary-600"></i>
                                <span class="font-medium text-gray-900">Delivery</span>
                            </div>
                            <input type="text" x-model="customerName" class="input text-sm" placeholder="Customer name">
                            <input type="text" x-model="customerPhone" class="input text-sm" placeholder="Phone number">
                        </div>
                    </template>
                </div>
                
                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-4 min-h-0 border-b" x-ref="cartContainer">
                    <h3 class="font-semibold text-gray-900 mb-3">Cart Items (<span x-text="cartItems.length"></span>)</h3>
                    
                    <template x-if="cartItems.length === 0">
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-shopping-cart text-5xl mb-3"></i>
                            <p>Cart is empty</p>
                        </div>
                    </template>
                    
                    <div class="space-y-3">
                        <template x-for="(item, index) in cartItems" :key="index">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium text-gray-900 text-sm" x-text="item.name"></h4>
                                    <button @click="removeFromCart(index)" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <button @click="decreaseQuantity(index)" class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300">
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <span class="w-8 text-center font-medium" x-text="item.quantity"></span>
                                        <button @click="increaseQuantity(index)" class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                    <span class="font-bold text-primary-600" x-text="`Rp ${formatMoney(item.price * item.quantity)}`"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Totals & Checkout -->
                <div class="border-t p-4 flex-shrink-0">
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium" x-text="`Rp ${formatMoney(subtotal)}`"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">
                                Tax (<span x-text="(taxRate * 100).toFixed(0)"></span>%)
                                <span class="text-xs" x-text="taxType === 'include' ? '(incl)' : ''"></span>
                            </span>
                            <span class="font-medium" x-text="`Rp ${formatMoney(tax)}`"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Discount</span>
                            <input type="number" 
                                   x-model="discount"
                                   @input="calculateTotals()"
                                   class="w-24 px-2 py-1 text-sm border rounded text-right"
                                   placeholder="0"
                                   min="0">
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t pt-2">
                            <span>Total</span>
                            <span class="text-primary-600" x-text="`Rp ${formatMoney(total)}`"></span>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <button @click="processCheckout()" 
                                :disabled="cartItems.length === 0"
                                :class="cartItems.length === 0 ? 'bg-gray-300 cursor-not-allowed' : 'bg-primary-600 hover:bg-primary-700'"
                                class="w-full text-white font-semibold py-3 rounded-lg transition">
                            <i class="fas fa-check-circle mr-2"></i>
                            Process Order
                        </button>
                        
                        <button @click="processOrderAndPayment()" 
                                :disabled="cartItems.length === 0"
                                :class="cartItems.length === 0 ? 'bg-gray-300 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                                class="w-full text-white font-semibold py-3 rounded-lg transition shadow-md">
                            <i class="fas fa-check-double mr-2"></i>
                            Process Order & Payment
                        </button>
                        
                        <button @click="cancelOrder()" 
                                class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 rounded-lg transition">
                            <i class="fas fa-times-circle mr-2"></i>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function posApp() {
    return {
        step: 'table', // 'table' or 'menu'
        products: @json($products),
        packages: @json($packages),
        filteredProducts: [],
        filteredPackages: [],
        searchQuery: '',
        showKeyboard: false,
        selectedCategory: 'favorite',
        cartItems: [],
        orderType: 'dine_in',
        tableId: '',
        selectedTableNumber: '',
        customerName: '',
        customerPhone: '',
        subtotal: 0,
        tax: 0,
        discount: 0,
        total: 0,
        taxRate: {{ \App\Models\Setting::get('tax_percentage', '10') }} / 100,
        typeChar(char) {
            this.searchQuery += char;
            this.filterProducts();
        },
        deleteChar() {
            this.searchQuery = this.searchQuery.slice(0, -1);
            this.filterProducts();
        },
        clearSearch() {
            this.searchQuery = '';
            this.filterProducts();
        },
        taxType: '{{ \App\Models\Setting::get('tax_type', 'exclude') }}',
        flag: false,
        syncDebounceTimer: null,
        displayMode: '{{ \App\Models\Setting::get('customer_display_mode', 'local') }}',
        broadcastChannel: null,
        orderLimitEnabled: {{ $orderLimitEnabled ? 'true' : 'false' }},
        orderLimitAmount: {{ $orderLimitAmount }},
        orderLimitStart: '{{ $orderLimitStart }}',
        orderLimitEnd: '{{ $orderLimitEnd }}',
        todayOrderTotal: {{ $todayOrderTotal }},
        
        init() {
            console.log('POS App initialized');
            console.log('Total products:', this.products.length);
            console.log('flag initialized:', this.flag);
            console.log('Display mode:', this.displayMode);
            
            // Init BroadcastChannel for local mode (real-time, same browser)
            if (typeof BroadcastChannel !== 'undefined') {
                this.broadcastChannel = new BroadcastChannel('pos_customer_display');
            }
            
            this.filterProducts();
            
            // If no favorites found, switch to All
            if (this.selectedCategory === 'favorite' && this.filteredProducts.length === 0) {
                console.log('No favorite products found, switching to All');
                this.selectedCategory = null;
                this.filterProducts();
            }
        },
        
        handleTableClick(tableId, tableNumber, status, orderId) {
            if (status === 'available') {
                // Table kosong - buat order baru
                this.selectTable(tableId, tableNumber);
            } else {
                // Table sudah ada order - langsung redirect ke edit order
                if (orderId) {
                    window.location.href = `/orders/${orderId}/edit`;
                }
            }
        },
        
        selectTable(tableId, tableNumber) {
            this.tableId = tableId;
            this.selectedTableNumber = tableNumber;
            this.step = 'menu';
        },
        
        backToTableSelection() {
            if (this.orderType === 'dine_in') {
                this.step = 'table';
                this.tableId = '';
                this.selectedTableNumber = '';
            } else {
                this.orderType = 'dine_in';
                this.step = 'table';
            }
            this.cartItems = [];
            this.calculateTotals();
        },
        
        filterProducts() {
            // Filter packages too
            this.filteredPackages = this.packages.filter(pkg => {
                if (!this.searchQuery) return true;
                return pkg.name.toLowerCase().includes(this.searchQuery.toLowerCase());
            });

            this.filteredProducts = this.products.filter(product => {
                let matchesCategory = true;
                
                if (this.selectedCategory === 'favorite') {
                    matchesCategory = product.is_favorite === true || product.is_favorite === 1;
                } else if (this.selectedCategory !== null && this.selectedCategory !== 'package') {
                    matchesCategory = product.category_id === this.selectedCategory;
                }
                
                let matchesSearch = !this.searchQuery || product.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchesCategory && matchesSearch;
            });
            
            console.log('Filter applied:', {
                category: this.selectedCategory,
                search: this.searchQuery,
                totalProducts: this.products.length,
                filteredCount: this.filteredProducts.length,
                filteredPackages: this.filteredPackages.length
            });
        },
        
        addToCart(product) {
            console.log('Adding to cart:', product);
            
            const existingItem = this.cartItems.find(item => item.product_id === product.id);
            
            if (existingItem) {
                existingItem.quantity++;
                console.log('Updated quantity:', existingItem);
            } else {
                this.cartItems.push({
                    product_id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1
                });
                console.log('New item added, cart length:', this.cartItems.length);
            }
            
            this.calculateTotals();
            this.$nextTick(() => { this.$refs.cartContainer.scrollTop = this.$refs.cartContainer.scrollHeight; });
        },
        
        addPackageToCart(pkg) {
            console.log('Adding package to cart:', pkg);
            
            // Calculate total normal price of package items
            const normalPrice = pkg.items.reduce((sum, item) => {
                return sum + (item.product ? parseFloat(item.product.price) * item.quantity : 0);
            }, 0);

            // Calculate price ratio for prorating
            const ratio = normalPrice > 0 ? parseFloat(pkg.price) / normalPrice : 1;

            // Add each package item to cart with prorated price
            pkg.items.forEach(item => {
                if (!item.product) return;
                
                const proratedPrice = Math.round(parseFloat(item.product.price) * ratio);
                const pkgLabel = `[${pkg.name}] ${item.product.name}`;
                
                const existingItem = this.cartItems.find(ci => ci.name === pkgLabel && ci.price === proratedPrice);
                
                if (existingItem) {
                    existingItem.quantity += item.quantity;
                } else {
                    this.cartItems.push({
                        product_id: item.product.id,
                        name: pkgLabel,
                        price: proratedPrice,
                        quantity: item.quantity
                    });
                }
            });
            
            this.calculateTotals();
            this.$nextTick(() => { this.$refs.cartContainer.scrollTop = this.$refs.cartContainer.scrollHeight; });
        },
        
        removeFromCart(index) {
            this.cartItems.splice(index, 1);
            this.calculateTotals();
        },
        
        increaseQuantity(index) {
            this.cartItems[index].quantity++;
            this.calculateTotals();
        },
        
        decreaseQuantity(index) {
            if (this.cartItems[index].quantity > 1) {
                this.cartItems[index].quantity--;
            } else {
                this.removeFromCart(index);
            }
            this.calculateTotals();
        },
        
        calculateTotals() {
            const itemsTotal = this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            if (this.taxType === 'include') {
                // Harga sudah termasuk PPN: extract DPP dan PPN dari harga
                this.subtotal = Math.round(itemsTotal / (1 + this.taxRate));
                this.tax = itemsTotal - this.subtotal;
                this.total = itemsTotal - (this.discount || 0);
            } else {
                // Harga belum termasuk PPN: PPN ditambahkan di atas
                this.subtotal = itemsTotal;
                this.tax = Math.round(this.subtotal * this.taxRate);
                this.total = this.subtotal + this.tax - (this.discount || 0);
            }
            
            console.log('Totals calculated:', {
                items: this.cartItems.length,
                subtotal: this.subtotal,
                tax: this.tax,
                flag: this.flag,
                total: this.total
            });
            
            // Debounced sync to customer display (wait 200ms before syncing)
            this.debouncedSync();
        },
        
        debouncedSync() {
            // Clear previous timer
            if (this.syncDebounceTimer) {
                clearTimeout(this.syncDebounceTimer);
            }
            // Set new timer
            this.syncDebounceTimer = setTimeout(() => {
                this.syncToCustomerDisplay();
            }, 200);
        },
        
        syncToCustomerDisplay() {
            const displayData = {
                cartItems: this.cartItems,
                subtotal: this.subtotal,
                tax: this.tax,
                discount: this.discount,
                total: this.total,
                taxRate: this.taxRate,
                taxType: this.taxType,
                orderType: this.orderType,
                tableNumber: this.selectedTableNumber,
                customerName: this.customerName,
                mode: '',
                orderNumber: ''
            };
            
            // Always set localStorage (works for local same-browser tabs)
            localStorage.setItem('pos_customer_display', JSON.stringify(displayData));
            
            // BroadcastChannel: instant push to other tabs (local mode, real-time)
            if (this.broadcastChannel) {
                try {
                    this.broadcastChannel.postMessage(displayData);
                } catch (e) {
                    console.error('BroadcastChannel error:', e);
                }
            }
            
            // Server sync: only if network mode (for cross-device access)
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
        
        async openCustomerDisplay() {
            const url = '{{ route('pos.customerDisplay') }}';
            
            try {
                // Fitur canggih jika menggunakan HTTPS
                if ('getScreenDetails' in window) {
                    const screenDetails = await window.getScreenDetails();
                    const externalScreen = screenDetails.screens.find(s => s !== screenDetails.currentScreen);
                    
                    if (externalScreen) {
                        window.open(url, 'CustomerDisplayWin', `left=${externalScreen.availLeft},top=${externalScreen.availTop},width=${externalScreen.availWidth},height=${externalScreen.availHeight},menubar=no,toolbar=no,location=no,status=no`);
                        return;
                    }
                }
            } catch (err) {
                console.log("Window API ditolak / HTTP biasa:", err);
            }

            // Fallback Ekstrem untuk HTTP Laragon / Localhost biasa
            const monitorLebar = window.screen.availWidth || 1024; 
            
            // Buka jendela
            let popup = window.open(url, 'CustomerDisplayWin', `width=1000,height=700,menubar=no,toolbar=no,location=no,status=no,resizable=yes`);
            
            if(popup) {
                setTimeout(() => {
                    // Paksa geser jendela ke arah monitor kedua (layar kanan)
                    popup.moveTo(monitorLebar, 0);
                    popup.focus();
                }, 500);
            } else {
                alert("Gagal membuka layar kedua. Mohon matikan POP-UP BLOCKER di ujung kanan atas URL bar browser Anda.");
            }
        },
        
        async processCheckout() {
            if (this.cartItems.length === 0) return;
            
            if (this.orderType === 'dine_in' && !this.tableId) {
                alert('Please select a table');
                return;
            }

            // Check order limit warning (rupiah-based, time-range aware)
            if (this.isOrderLimitActive() && this.orderLimitAmount > 0 && this.todayOrderTotal >= this.orderLimitAmount) {
                if (!confirm(`⚠️ Peringatan: Total penjualan hari ini sudah mencapai limit (${this.formatRupiah(this.todayOrderTotal)} / ${this.formatRupiah(this.orderLimitAmount)}).\nBerlaku: ${this.orderLimitStart} - ${this.orderLimitEnd}\n\nApakah tetap ingin melanjutkan order?`)) {
                    return;
                }
            }
            
            const orderData = {
                type: this.orderType,
                table_id: this.orderType === 'dine_in' ? this.tableId : null,
                customer_name: this.customerName,
                customer_phone: this.customerPhone,
                tax: this.taxRate * 100, // Send tax rate percentage
                discount: this.discount || 0,
                flag: this.flag,
                items: this.cartItems.map(item => ({
                    product_id: item.product_id,
                    name: item.name,
                    price: item.price,
                    quantity: item.quantity
                }))
            };
            
            try {
                await this.createOrder(orderData, false);
            } catch (error) {
                console.error('Error creating order:', error);
            }
        },
        
        async processOrderAndPayment() {
            if (this.cartItems.length === 0) return;
            
            if (this.orderType === 'dine_in' && !this.tableId) {
                alert('Please select a table');
                return;
            }

            // Check order limit warning (rupiah-based, time-range aware)
            if (this.isOrderLimitActive() && this.orderLimitAmount > 0 && this.todayOrderTotal >= this.orderLimitAmount) {
                if (!confirm(`⚠️ Peringatan: Total penjualan hari ini sudah mencapai limit (${this.formatRupiah(this.todayOrderTotal)} / ${this.formatRupiah(this.orderLimitAmount)}).\nBerlaku: ${this.orderLimitStart} - ${this.orderLimitEnd}\n\nApakah tetap ingin melanjutkan order?`)) {
                    return;
                }
            }
            
            const orderData = {
                type: this.orderType,
                table_id: this.orderType === 'dine_in' ? this.tableId : null,
                customer_name: this.customerName,
                customer_phone: this.customerPhone,
                tax: this.taxRate * 100, // Send tax rate percentage
                discount: this.discount || 0,
                flag: this.flag,
                items: this.cartItems.map(item => ({
                    product_id: item.product_id,
                    name: item.name,
                    price: item.price,
                    quantity: item.quantity
                }))
            };
            
            try {
                await this.createOrder(orderData, true);
            } catch (error) {
                console.error('Error creating order:', error);
            }
        },
        
        async createOrder(orderData, redirectToPayment = false) {
            try {
                console.log('Sending order data:', orderData);
                
                const response = await fetch('{{ route('pos.createOrder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(orderData)
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers.get('content-type'));
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    console.error('Non-JSON response:', textResponse.substring(0, 500));
                    alert('Server error. Please check if server is running properly.');
                    return;
                }
                
                const result = await response.json();
                
                console.log('Response data:', result);
                
                if (response.ok && result.success) {
                    // Clear customer display immediately
                    const emptyData = {
                        cartItems: [], subtotal: 0, tax: 0, discount: 0, total: 0,
                        taxRate: this.taxRate, orderType: '', tableNumber: '',
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
                    
                    // Redirect based on parameter
                    if (redirectToPayment && result.data && result.data.id) {
                        window.location.href = `/orders/${result.data.id}/payment`;
                    } else {
                        // Update today's total after successful order
                        this.todayOrderTotal += this.total;
                        window.location.href = result.redirect;
                    }
                } else {
                    let errorMsg = result.message || result.error || 'Failed to create order';
                    
                    // Add validation errors if present
                    if (result.errors) {
                        errorMsg += '\n\nValidation Errors:\n';
                        for (const [field, messages] of Object.entries(result.errors)) {
                            errorMsg += `- ${field}: ${messages.join(', ')}\n`;
                        }
                    }
                    
                    alert('Error: ' + errorMsg);
                    console.error('Server response:', result);
                    console.error('Validation errors:', result.errors);
                }
            } catch (error) {
                console.error('Caught error:', error);
                console.error('Error type:', error.constructor.name);
                console.error('Error message:', error.message);
                alert('Failed to create order. Please check console for details.');
            }
        },
        
        resetCart() {
            this.cartItems = [];
            this.customerName = '';
            this.customerPhone = '';
            this.discount = 0;
            this.flag = false;
            this.calculateTotals();
            
            // Clear customer display (local + broadcast + conditional server)
            const emptyData = {
                cartItems: [], subtotal: 0, tax: 0, discount: 0, total: 0,
                taxRate: this.taxRate, orderType: '', tableNumber: '',
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
                }).catch(err => console.error('Failed to clear server data:', err));
            }
        },
        
        cancelOrder() {
            // Langsung batalkan pesanan tanpa memunculkan Confirm/Alert pop-up yang mengganggu mode Fullscreen layar kedua
            this.resetCart();
            this.step = 'table';
            this.tableId = '';
            this.selectedTableNumber = '';
            this.orderType = 'dine_in';
        },
        
        formatMoney(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        },

        formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        },

        isOrderLimitActive() {
            if (!this.orderLimitEnabled) return false;
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${hh}:${mm}`;
            return currentTime >= this.orderLimitStart && currentTime <= this.orderLimitEnd;
        }
    }
}
</script>
@endpush
@endsection
