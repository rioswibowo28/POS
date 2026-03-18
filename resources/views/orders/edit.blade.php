@extends('layouts.app')

@section('title', 'Edit Order')
@section('header', 'Edit Order #' . $order->order_number)

@section('content')
<div x-data="editOrderApp()" x-init="init()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Products & Search -->
    <div class="lg:col-span-2">
        <div class="card h-full flex flex-col">
            
            <!-- Search & Categories -->
            <div class="border-b pb-4 mb-4">
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
                    <button @click="selectedCategory = 'favorit'; filterProducts()"
                            :class="selectedCategory === 'favorit' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg whitespace-nowrap text-sm font-medium transition">
                        <i class="fas fa-star mr-1"></i> Favorit
                    </button>
                    <button @click="selectedCategory = null; filterProducts()"
                            :class="selectedCategory === null ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg whitespace-nowrap text-sm font-medium transition">
                        All
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
            <div class="flex-1 overflow-y-auto">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product.id, product.name, product.price)" 
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
    
    <!-- Order Summary -->
    <div class="space-y-6">
        <div class="card">
            <h3 class="text-lg font-semibold mb-4">Order Details</h3>
            
            <form @submit.prevent="updateOrder()">
                <!-- Table Selection (if dine in) -->
                @if($order->type->value === 'dine_in')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Table</label>
                    <select x-model="tableId" class="input">
                        <option value="">Select Table</option>
                        @foreach($tables as $table)
                        <option value="{{ $table->id }}">Table {{ $table->number }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <!-- Customer Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name</label>
                    <input type="text" x-model="customerName" @input="syncToCustomerDisplay()" class="input" placeholder="Optional">
                </div>
                
                <!-- Notes -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea x-model="notes" rows="2" class="input" placeholder="Optional"></textarea>
                </div>
                
                <!-- Cart Items -->
                <div class="mb-4">
                    <h4 class="font-semibold mb-3">Cart Items</h4>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <template x-for="(item, index) in cart" :key="index">
                            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                                <div class="flex-1">
                                    <p class="text-sm font-medium" x-text="item.name"></p>
                                    <p class="text-xs text-gray-500" x-text="`Rp ${formatMoney(item.price)}`"></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="decreaseQty(index)" class="w-6 h-6 bg-gray-200 rounded text-sm">-</button>
                                    <span class="w-8 text-center text-sm" x-text="item.quantity"></span>
                                    <button type="button" @click="increaseQty(index)" class="w-6 h-6 bg-gray-200 rounded text-sm">+</button>
                                    <button type="button" @click="removeItem(index)" class="w-6 h-6 bg-red-500 text-white rounded text-sm">×</button>
                                </div>
                            </div>
                        </template>
                        
                        <template x-if="cart.length === 0">
                            <p class="text-center text-gray-400 py-4 text-sm">No items in cart</p>
                        </template>
                    </div>
                </div>
                
                <!-- Total -->
                <div class="border-t pt-4 space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal</span>
                        <span x-text="`Rp ${formatMoney(subtotal)}`"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>
                            Tax (<span x-text="(taxRate * 100).toFixed(0)"></span>%)
                            <span class="text-xs" x-text="taxType === 'include' ? '(incl)' : ''"></span>
                        </span>
                        <span x-text="`Rp ${formatMoney(tax)}`"></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                        <span>Total</span>
                        <span class="text-primary-600" x-text="`Rp ${formatMoney(total)}`"></span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="space-y-2">
                    <button type="submit" 
                            :disabled="cart.length === 0"
                            :class="cart.length === 0 ? 'bg-gray-300 cursor-not-allowed' : 'bg-primary-600 hover:bg-primary-700'"
                            class="w-full text-white font-semibold py-3 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i> Update Order
                    </button>
                    <a href="{{ route('orders.payment', $order->id) }}" 
                       style="background-color: #16a34a; color: white;"
                       class="block w-full hover:bg-green-700 text-white font-semibold text-center py-3 rounded-lg transition shadow-md">
                        <i class="fas fa-credit-card mr-2"></i> Process Payment
                    </a>
                    <a href="{{ route('pos.index') }}" class="block w-full btn-secondary text-center py-3">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editOrderApp() {
    return {
        // Products & Filter
        products: {!! json_encode($products->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'image' => $p->image,
                'category_id' => $p->category_id,
                'is_favorite' => $p->is_favorite,
                'inventory' => $p->inventory ? [
                    'quantity' => $p->inventory->quantity,
                    'min_quantity' => $p->inventory->min_quantity
                ] : null
            ];
        })) !!},
        filteredProducts: [],
        searchQuery: '',
        showKeyboard: false,
        selectedCategory: 'favorit',

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

        // Cart & Order
        cart: {!! json_encode($order->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity
            ];
        })) !!},
        tableId: {{ $order->table_id ?? 'null' }},
        customerName: '{{ $order->customer_name ?? '' }}',
        notes: '{{ $order->notes ?? '' }}',
        orderType: '{{ $order->type->value }}',
        taxRate: {{ \App\Models\Setting::get('tax_percentage', '10') }} / 100,
        taxType: '{{ \App\Models\Setting::get('tax_type', 'exclude') }}',
        
        init() {
            this.filterProducts();
            this.syncToCustomerDisplay();
        },
        
        syncToCustomerDisplay() {
            const displayData = {
                mode: 'edit',
                orderNumber: '{{ $order->order_number }}',
                orderType: this.orderType,
                tableNumber: '{{ $order->table ? $order->table->number : "" }}',
                customerName: this.customerName,
                cartItems: this.cart.map(item => ({
                    product_id: item.product_id,
                    name: item.name,
                    price: item.price,
                    quantity: item.quantity
                })),
                subtotal: this.subtotal,
                tax: this.tax,
                discount: 0,
                total: this.total,
                taxRate: this.taxRate
            };
            localStorage.setItem('pos_customer_display', JSON.stringify(displayData));
            
            // Also sync to server for cross-device support
            fetch('/api/customer-display/data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(displayData)
            }).catch(err => console.error('Failed to sync to server:', err));
        },
        
        filterProducts() {
            this.filteredProducts = this.products.filter(product => {
                const matchesSearch = this.searchQuery === '' || 
                    product.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                
                let matchesCategory;
                if (this.selectedCategory === 'favorit') {
                    matchesCategory = product.is_favorite === true;
                } else if (this.selectedCategory === null) {
                    matchesCategory = true;
                } else {
                    matchesCategory = product.category_id === this.selectedCategory;
                }
                
                return matchesSearch && matchesCategory;
            });
        },
        
        get subtotal() {
            const itemsTotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            if (this.taxType === 'include') {
                return Math.round(itemsTotal / (1 + this.taxRate));
            }
            return itemsTotal;
        },
        
        get tax() {
            const itemsTotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            if (this.taxType === 'include') {
                return itemsTotal - this.subtotal;
            }
            return Math.round(this.subtotal * this.taxRate);
        },
        
        get total() {
            const itemsTotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            if (this.taxType === 'include') {
                return itemsTotal;
            }
            return this.subtotal + this.tax;
        },
        
        addToCart(productId, name, price) {
            const existingItem = this.cart.find(item => item.product_id === productId);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                this.cart.push({
                    product_id: productId,
                    name: name,
                    price: price,
                    quantity: 1
                });
            }
            this.syncToCustomerDisplay();
        },
        
        increaseQty(index) {
            this.cart[index].quantity++;
            this.syncToCustomerDisplay();
        },
        
        decreaseQty(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity--;
            }
            this.syncToCustomerDisplay();
        },
        
        removeItem(index) {
            this.cart.splice(index, 1);
            this.syncToCustomerDisplay();
        },
        
        formatMoney(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        },
        
        updateOrder() {
            if (this.cart.length === 0) {
                alert('Please add items to cart');
                return;
            }
            
            const formData = new FormData();
            formData.append('_method', 'PUT');
            
            // Only append table_id if it has a value
            if (this.tableId) {
                formData.append('table_id', this.tableId);
            }
            
            formData.append('customer_name', this.customerName);
            formData.append('notes', this.notes);
            
            this.cart.forEach((item, index) => {
                formData.append(`items[${index}][product_id]`, item.product_id);
                formData.append(`items[${index}][quantity]`, item.quantity);
                formData.append(`items[${index}][price]`, item.price);
            });
            
            return fetch('{{ route("orders.update", $order->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.ok ? response : Promise.reject(response))
            .then(() => {
                // Clear customer display (local and server)
                localStorage.removeItem('pos_customer_display');
                fetch('/api/customer-display/data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        cartItems: [],
                        subtotal: 0,
                        tax: 0,
                        discount: 0,
                        total: 0,
                        taxRate: 0.10,
                        orderType: '',
                        tableNumber: '',
                        customerName: '',
                        mode: '',
                        orderNumber: ''
                    })
                }).catch(err => console.error('Failed to clear server:', err));
                
                window.location.href = '{{ route("pos.index") }}';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update order');
            });
        }
    }
}
</script>
@endsection
