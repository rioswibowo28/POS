@extends('layouts.app')

@section('title', 'Packages')
@section('header', 'Package Management')

@section('content')
<div x-data="packageManager()">
    
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <div class="card">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Semua Paket</h2>
                <p class="text-gray-600 text-sm">Kelola paket menu (bundling produk)</p>
            </div>
            <button @click="openCreateModal()" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Tambah Paket
            </button>
        </div>

        <!-- Filters -->
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <input type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Cari paket...">
            <select name="is_active" class="input">
                <option value="">Semua Status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
            <button type="submit" class="btn-secondary">
                <i class="fas fa-filter mr-2"></i> Filter
            </button>
        </form>

        <!-- Packages Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @forelse($packages as $package)
            <div class="border border-gray-200 rounded-lg p-3 hover:border-primary-500 hover:shadow-md transition-all duration-200 relative">
                <!-- Status Badge (Toggle) -->
                <div class="absolute top-2 right-2">
                    <form action="{{ route('packages.toggleActive', $package->id) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        @if($package->is_active)
                            <button type="submit" class="bg-green-100 text-green-700 text-[10px] font-semibold px-1.5 py-0.5 rounded-full hover:bg-green-200 transition cursor-pointer" title="Klik untuk nonaktifkan">Aktif</button>
                        @else
                            <button type="submit" class="bg-red-100 text-red-700 text-[10px] font-semibold px-1.5 py-0.5 rounded-full hover:bg-red-200 transition cursor-pointer" title="Klik untuk aktifkan">Tidak Aktif</button>
                        @endif
                    </form>
                </div>

                <!-- Package Image/Icon -->
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-purple-600 rounded-lg flex items-center justify-center mb-2">
                    @if($package->image)
                        <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="w-full h-full object-cover rounded-lg">
                    @else
                        <i class="fas fa-cubes text-sm text-white"></i>
                    @endif
                </div>

                <!-- Package Info -->
                <h3 class="font-bold text-gray-900 text-sm mb-0.5">{{ $package->name }}</h3>
                @if($package->description)
                    <p class="text-gray-500 text-xs mb-2 line-clamp-1">{{ $package->description }}</p>
                @endif

                <!-- Price -->
                <div class="mb-2">
                    <span class="text-lg font-bold text-primary-600">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                    @if($package->savings > 0)
                        <div class="flex items-center gap-1 mt-0.5">
                            <span class="text-xs text-gray-400 line-through">Rp {{ number_format($package->normal_price, 0, ',', '.') }}</span>
                            <span class="bg-red-100 text-red-600 text-[10px] font-semibold px-1.5 py-0.5 rounded-full">Hemat Rp {{ number_format($package->savings, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Items List -->
                <div class="bg-gray-50 rounded-md p-2 mb-2">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Isi Paket ({{ $package->items->count() }} item)</p>
                    <ul class="space-y-0.5">
                        @foreach($package->items as $item)
                        <li class="flex items-center justify-between text-xs">
                            <span class="text-gray-700">
                                <i class="fas fa-check text-green-500 mr-1 text-[10px]"></i>
                                {{ $item->product->name ?? 'Produk dihapus' }}
                            </span>
                            <span class="text-gray-400 text-[10px]">x{{ $item->quantity }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Actions -->
                <div class="flex gap-1.5">
                    <button @click="openEditModal({{ $package->id }})" class="flex-1 bg-blue-50 text-blue-600 hover:bg-blue-100 font-medium py-1.5 px-2 rounded-md text-xs transition">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                    <button @click="confirmDelete({{ $package->id }}, '{{ addslashes($package->name) }}')" class="bg-red-50 text-red-600 hover:bg-red-100 font-medium py-1.5 px-2 rounded-md text-xs transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <i class="fas fa-cubes text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">Belum ada paket</p>
                <p class="text-gray-400 text-sm">Klik "Tambah Paket" untuk membuat paket pertama</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $packages->links() }}
        </div>
    </div>

    <!-- ======================== CREATE / EDIT MODAL ======================== -->
    <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeModal()">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-4" x-transition>
            <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-2xl z-10">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-900" x-text="editMode ? 'Edit Paket' : 'Tambah Paket Baru'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>
            </div>

            <form :action="editMode ? '/packages/' + editId : '/packages'" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="form.name" class="input w-full" placeholder="Contoh: Paket Hemat" required>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" x-model="form.description" class="input w-full" rows="2" placeholder="Deskripsi singkat paket (opsional)"></textarea>
                </div>

                <!-- Image -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Gambar</label>
                    <input type="file" name="image" accept="image/*" class="input w-full">
                    <template x-if="editMode && form.image">
                        <div class="mt-2">
                            <img :src="'/storage/' + form.image" class="w-20 h-20 object-cover rounded-lg border">
                        </div>
                    </template>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Harga Paket <span class="text-red-500">*</span></label>
                    <input type="number" name="price" x-model="form.price" class="input w-full" min="0" step="100" placeholder="0" required>
                    <template x-if="computedNormalPrice > 0">
                        <p class="text-sm text-gray-500 mt-1">
                            Harga normal: <span class="font-semibold" x-text="'Rp ' + formatMoney(computedNormalPrice)"></span>
                            <template x-if="form.price > 0 && computedNormalPrice > form.price">
                                <span class="text-green-600 font-semibold"> — Hemat <span x-text="'Rp ' + formatMoney(computedNormalPrice - form.price)"></span></span>
                            </template>
                        </p>
                    </template>
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">Aktif</span>
                </div>

                <!-- Package Items -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Isi Paket <span class="text-red-500">*</span></label>
                    
                    <!-- Add Item Row -->
                    <div class="flex gap-2 mb-3">
                        <select x-model="newItemProductId" class="input flex-1">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}">
                                    {{ $product->name }} — Rp {{ number_format($product->price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <input type="number" x-model="newItemQty" class="input w-20" min="1" value="1" placeholder="Qty">
                        <button type="button" @click="addItem()" class="btn-primary px-4" :disabled="!newItemProductId">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-2">
                        <template x-for="(item, index) in form.items" :key="index">
                            <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-3 border">
                                <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800" x-text="item.product_name"></span>
                                    <span class="text-gray-400 text-sm ml-2" x-text="'@ Rp ' + formatMoney(item.product_price)"></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="item.quantity > 1 ? item.quantity-- : null" class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded text-gray-600 text-xs transition">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" min="1" class="w-14 text-center input text-sm py-1">
                                    <button type="button" @click="item.quantity++" class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded text-gray-600 text-xs transition">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <span class="text-sm font-semibold text-gray-600 w-28 text-right" x-text="'Rp ' + formatMoney(item.product_price * item.quantity)"></span>
                                <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 transition">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <template x-if="form.items.length === 0">
                        <div class="text-center py-6 text-gray-400 bg-gray-50 rounded-lg border-2 border-dashed">
                            <i class="fas fa-box-open text-2xl mb-2"></i>
                            <p class="text-sm">Belum ada item. Pilih produk di atas untuk menambahkan.</p>
                        </div>
                    </template>
                </div>

                <!-- Submit -->
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="closeModal()" class="btn-secondary flex-1">Batal</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="form.items.length === 0">
                        <i class="fas fa-save mr-2"></i>
                        <span x-text="editMode ? 'Update Paket' : 'Simpan Paket'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================== DELETE CONFIRM MODAL ======================== -->
    <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showDeleteModal = false">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6" x-transition>
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Paket?</h3>
                <p class="text-gray-500 mb-6">Apakah kamu yakin ingin menghapus paket <strong x-text="deleteName"></strong>?</p>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false" class="btn-secondary flex-1">Batal</button>
                    <form :action="'/packages/' + deleteId" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg w-full transition">
                            <i class="fas fa-trash mr-2"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function packageManager() {
    const allProducts = @json($products);
    
    return {
        showModal: false,
        showDeleteModal: false,
        editMode: false,
        editId: null,
        deleteId: null,
        deleteName: '',
        newItemProductId: '',
        newItemQty: 1,
        form: {
            name: '',
            description: '',
            image: null,
            price: '',
            is_active: true,
            items: [],
        },

        get computedNormalPrice() {
            return this.form.items.reduce((sum, item) => {
                return sum + (parseFloat(item.product_price) * parseInt(item.quantity));
            }, 0);
        },

        openCreateModal() {
            this.editMode = false;
            this.editId = null;
            this.resetForm();
            this.showModal = true;
        },

        async openEditModal(id) {
            try {
                const res = await fetch('/packages/' + id + '/edit');
                const data = await res.json();
                const pkg = data.package;

                this.editMode = true;
                this.editId = id;
                this.form.name = pkg.name;
                this.form.description = pkg.description || '';
                this.form.image = pkg.image;
                this.form.price = pkg.price;
                this.form.is_active = pkg.is_active;
                this.form.items = pkg.items.map(item => ({
                    product_id: item.product_id,
                    product_name: item.product ? item.product.name : 'Produk dihapus',
                    product_price: item.product ? parseFloat(item.product.price) : 0,
                    quantity: item.quantity,
                }));

                this.showModal = true;
            } catch (err) {
                console.error('Error loading package:', err);
                alert('Gagal memuat data paket');
            }
        },

        addItem() {
            if (!this.newItemProductId) return;

            const productId = parseInt(this.newItemProductId);
            const qty = parseInt(this.newItemQty) || 1;

            // Check if already exists
            const existing = this.form.items.find(i => i.product_id === productId);
            if (existing) {
                existing.quantity += qty;
                this.newItemProductId = '';
                this.newItemQty = 1;
                return;
            }

            const product = allProducts.find(p => p.id === productId);
            if (!product) return;

            this.form.items.push({
                product_id: productId,
                product_name: product.name,
                product_price: parseFloat(product.price),
                quantity: qty,
            });

            this.newItemProductId = '';
            this.newItemQty = 1;
        },

        removeItem(index) {
            this.form.items.splice(index, 1);
        },

        confirmDelete(id, name) {
            this.deleteId = id;
            this.deleteName = name;
            this.showDeleteModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.editMode = false;
            this.editId = null;
        },

        resetForm() {
            this.form = {
                name: '',
                description: '',
                image: null,
                price: '',
                is_active: true,
                items: [],
            };
            this.newItemProductId = '';
            this.newItemQty = 1;
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        },
    };
}
</script>
@endpush
