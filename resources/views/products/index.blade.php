@extends('layouts.app')

@section('title', 'Products')
@section('header', 'Product Management')

@section('content')
<div x-data="productManager()">
    
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
                <h2 class="text-xl font-bold text-gray-900">Semua Produk</h2>
                <p class="text-gray-600 text-sm">Kelola menu dan produk</p>
            </div>
            <div class="flex gap-2">
                <button @click="openBulkInsertModal()" class="text-white font-semibold py-2 px-4 rounded-lg shadow transition flex items-center" style="background-color: #6366f1;">
                    <i class="fas fa-layer-group mr-2"></i> Bulk Insert
                </button>
                <button @click="openCreateModal()" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i> Tambah Produk
                </button>
            </div>
        </div>

        <!-- Bulk Action Toolbar -->
        <div x-show="selectedIds.length > 0" x-transition
             class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg p-3 flex flex-wrap items-center gap-3 relative z-40">
            <div class="flex items-center gap-2">
                <span class="bg-indigo-500 text-white text-xs font-bold px-2 py-1 rounded-full" x-text="selectedIds.length"></span>
                <span class="text-sm font-medium text-indigo-800">produk dipilih</span>
            </div>
            <div class="flex-1"></div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Bulk Update Actions -->
                <div class="relative" x-data="{ openDropdown: false }">
                    <button @click="openDropdown = !openDropdown" class="bg-white border border-indigo-300 text-indigo-700 hover:bg-indigo-100 text-sm font-medium py-1.5 px-3 rounded-lg flex items-center gap-1 transition">
                        <i class="fas fa-edit"></i> Bulk Update <i class="fas fa-caret-down"></i>
                    </button>
                    <div x-show="openDropdown" @click.outside="openDropdown = false" x-transition
                         class="absolute right-0 top-full mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-xl z-50 py-1">
                        <button @click="bulkAction('activate'); openDropdown = false" class="w-full text-left px-4 py-2 text-sm hover:bg-green-50 text-gray-700">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i> Aktifkan
                        </button>
                        <button @click="bulkAction('deactivate'); openDropdown = false" class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-gray-700">
                            <i class="fas fa-times-circle text-red-500 mr-2"></i> Nonaktifkan
                        </button>
                        <hr class="my-1">
                        <button @click="bulkAction('set_available'); openDropdown = false" class="w-full text-left px-4 py-2 text-sm hover:bg-green-50 text-gray-700">
                            <i class="fas fa-eye text-green-500 mr-2"></i> Set Tersedia
                        </button>
                        <button @click="bulkAction('set_unavailable'); openDropdown = false" class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-gray-700">
                            <i class="fas fa-eye-slash text-red-500 mr-2"></i> Set Tidak Tersedia
                        </button>
                        <hr class="my-1">
                        <button @click="bulkAction('set_favorite'); openDropdown = false" class="w-full text-left px-4 py-2 text-sm hover:bg-yellow-50 text-gray-700">
                            <i class="fas fa-star text-yellow-500 mr-2"></i> Set Favorit
                        </button>
                        <button @click="bulkAction('unset_favorite'); openDropdown = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 text-gray-700">
                            <i class="far fa-star text-gray-400 mr-2"></i> Hapus Favorit
                        </button>
                        <hr class="my-1">
                        <button @click="openBulkCategoryModal(); openDropdown = false" class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 text-gray-700">
                            <i class="fas fa-folder text-blue-500 mr-2"></i> Ubah Kategori
                        </button>
                        <button @click="openBulkPriceModal(); openDropdown = false" class="w-full text-left px-4 py-2 text-sm hover:bg-purple-50 text-gray-700">
                            <i class="fas fa-tag text-purple-500 mr-2"></i> Sesuaikan Harga
                        </button>
                    </div>
                </div>

                <!-- Bulk Delete -->
                <button @click="confirmBulkDelete()" class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-1.5 px-3 rounded-lg flex items-center gap-1 transition">
                    <i class="fas fa-trash"></i> Hapus
                </button>

                <!-- Deselect All -->
                <button @click="selectedIds = []" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium py-1.5 px-3 rounded-lg transition">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </div>
        
        <!-- Filters -->
        <form method="GET" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-3">
                <input type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Cari produk...">
                <select name="category_id" class="input">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                <select name="is_active" class="input">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <select name="is_favorite" class="input">
                    <option value="">Semua Produk</option>
                    <option value="1" {{ request('is_favorite') === '1' ? 'selected' : '' }}>⭐ Favorit Saja</option>
                </select>
                <button type="submit" class="btn-secondary">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
            </div>
        </form>

        <!-- Select All -->
        <div class="flex items-center gap-3 mb-3">
            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600 hover:text-gray-900">
                <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       @change="toggleSelectAll($event)" :checked="allSelected">
                <span>Pilih Semua</span>
            </label>
            <span class="text-xs text-gray-400" x-show="selectedIds.length > 0" x-text="selectedIds.length + ' dipilih'"></span>
        </div>
        
        <!-- Products Grid -->
        <div class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
            @forelse($products as $product)
            <div class="border rounded p-2 hover:border-primary-500 transition relative" style="max-width: 300px;"
                 :class="selectedIds.includes({{ $product->id }}) ? 'border-indigo-500 bg-indigo-50/50 ring-1 ring-indigo-300' : 'border-gray-200'">
                
                <!-- Checkbox -->
                <div class="absolute top-1 left-1 z-10">
                    <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                           value="{{ $product->id }}" 
                           @change="toggleSelect({{ $product->id }})"
                           :checked="selectedIds.includes({{ $product->id }})">
                </div>

                <div class="aspect-square bg-gray-100 rounded mb-1.5 flex items-center justify-center overflow-hidden">
                    @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                    <i class="fas fa-utensils text-xl text-gray-300"></i>
                    @endif
                </div>
                
                <div class="mb-1.5">
                    <div class="flex items-center gap-1 mb-0.5">
                        <h4 class="font-semibold text-xs text-gray-900 line-clamp-2 leading-tight flex-1">{{ $product->name }}</h4>
                        @if($product->is_favorite)
                        <span class="text-yellow-500" title="Favorite">⭐</span>
                        @endif
                    </div>
                    <p class="text-primary-600 font-bold text-xs">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                </div>
                
                <div class="flex gap-0.5 mb-1.5 justify-center">
                    @if($product->is_active)
                    <span class="w-4 h-4 flex items-center justify-center rounded bg-green-500 text-white" title="Aktif">
                        <i class="fas fa-check" style="font-size: 0.5rem;"></i>
                    </span>
                    @else
                    <span class="w-4 h-4 flex items-center justify-center rounded bg-gray-400 text-white" title="Nonaktif">
                        <i class="fas fa-times" style="font-size: 0.5rem;"></i>
                    </span>
                    @endif
                    
                    @if($product->inventory)
                        @php
                            $qty = $product->inventory->quantity;
                            $min = $product->inventory->min_quantity;
                            $isLowStock = $qty <= $min;
                            $isOutOfStock = $qty <= 0;
                        @endphp
                        
                        @if($isOutOfStock)
                            <span class="w-4 h-4 flex items-center justify-center rounded bg-red-500 text-white" title="Stok Habis">
                                <i class="fas fa-times" style="font-size: 0.5rem;"></i>
                            </span>
                        @elseif($isLowStock)
                            <span class="px-1 text-xs rounded bg-yellow-500 text-white" title="Stok Rendah: {{ $qty }}" style="font-size: 0.5rem; line-height: 1rem;">
                                {{ $qty }}
                            </span>
                        @else
                            <span class="px-1 text-xs rounded bg-blue-500 text-white" title="Stok: {{ $qty }}" style="font-size: 0.5rem; line-height: 1rem;">
                                {{ $qty }}
                            </span>
                        @endif
                    @endif
                </div>
                
                <div class="grid grid-cols-3 gap-1">
                    <button type="button" @click="openEditModal({{ $product->id }})" class="h-8 bg-green-500 hover:bg-green-600 text-white rounded flex items-center justify-center" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" @click="openStockModal({{ $product->id }})" class="h-8 bg-purple-500 hover:bg-purple-600 text-white rounded flex items-center justify-center" title="Stok">
                        <i class="fas fa-boxes"></i>
                    </button>
                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" 
                          onsubmit="return confirm('Hapus produk ini?')" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="h-8 w-full bg-red-500 hover:bg-red-600 text-white rounded flex items-center justify-center" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                <i class="fas fa-box-open text-5xl mb-3 block text-gray-300"></i>
                Tidak ada produk ditemukan
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($products->hasPages())
        <div class="mt-6">
            {{ $products->links() }}
        </div>
        @endif
    </div>
    
    <!-- Modal Add/Edit Product -->
    <div x-show="showModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="showModal = false">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold" x-text="editMode ? 'Edit Produk' : 'Tambah Produk Baru'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form :action="editMode ? `/products/${currentProduct.id}` : '{{ route('products.store') }}'" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" x-bind:value="editMode ? 'PUT' : 'POST'">
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                                <select name="category_id" class="input" required x-model="currentProduct.category_id">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Produk *</label>
                                <input type="text" name="name" class="input" required x-model="currentProduct.name">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea name="description" rows="3" class="input" x-model="currentProduct.description"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga *</label>
                                <input type="number" name="price" class="input" step="1000" required x-model="currentProduct.price">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Modal</label>
                                <input type="number" name="cost" class="input" step="1000" x-model="currentProduct.cost">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SKU</label>
                                <input type="text" name="sku" class="input" x-model="currentProduct.sku">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Produk</label>
                            <input type="file" name="image" accept="image/*" class="input">
                            <template x-if="editMode && currentProduct.image">
                                <div class="mt-2">
                                    <img :src="`/storage/${currentProduct.image}`" class="w-32 h-32 object-cover rounded">
                                </div>
                            </template>
                        </div>
                        
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" class="mr-2" x-bind:checked="currentProduct.is_active">
                                <span class="text-sm">Aktif</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_available" value="1" class="mr-2" x-bind:checked="currentProduct.is_available">
                                <span class="text-sm">Tersedia</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_favorite" value="1" class="mr-2" x-bind:checked="currentProduct.is_favorite">
                                <span class="text-sm">⭐ Favorit</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="flex-1 btn-primary">
                            <i class="fas fa-save mr-2"></i> 
                            <span x-text="editMode ? 'Update Produk' : 'Simpan Produk'"></span>
                        </button>
                        <button type="button" @click="showModal = false" class="flex-1 btn-secondary">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Manage Stock -->
    <div x-show="showStockModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="showStockModal = false">
        <div class="bg-white rounded-lg max-w-md w-full" @click.stop>
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Kelola Stok</h3>
                    <button @click="showStockModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-900 mb-1" x-text="stockProduct.name"></h4>
                    <p class="text-sm text-gray-600" x-text="stockProduct.category_name"></p>
                </div>
                
                <form :action="`/products/${stockProduct.id}/stock`" method="POST">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Stok Saat Ini *</label>
                            <input type="number" name="quantity" class="input" required min="0" step="1" x-model="stockProduct.quantity">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Stok Alert *</label>
                            <input type="number" name="min_quantity" class="input" required min="0" step="1" x-model="stockProduct.min_quantity">
                            <p class="text-xs text-gray-500 mt-1">Peringatan saat stok mencapai level ini</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Satuan *</label>
                            <select name="unit" class="input" required x-model="stockProduct.unit">
                                <option value="porsi">Porsi</option>
                                <option value="gelas">Gelas</option>
                                <option value="pcs">Pcs</option>
                                <option value="pack">Pack</option>
                                <option value="box">Box</option>
                                <option value="kg">Kg</option>
                                <option value="liter">Liter</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="flex-1 btn-primary">
                            <i class="fas fa-save mr-2"></i> Update Stok
                        </button>
                        <button type="button" @click="showStockModal = false" class="flex-1 btn-secondary">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Bulk Insert -->
    <div x-show="showBulkInsertModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="showBulkInsertModal = false">
        <div class="bg-white rounded-lg max-w-5xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Bulk Insert Produk</h3>
                        <p class="text-sm text-gray-500">Tambahkan beberapa produk sekaligus</p>
                    </div>
                    <button @click="showBulkInsertModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form action="{{ route('products.bulkStore') }}" method="POST">
                    @csrf
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="text-left py-2 px-2 font-medium text-gray-600 w-8">#</th>
                                    <th class="text-left py-2 px-2 font-medium text-gray-600">Nama Produk *</th>
                                    <th class="text-left py-2 px-2 font-medium text-gray-600">Kategori *</th>
                                    <th class="text-left py-2 px-2 font-medium text-gray-600">Harga *</th>
                                    <th class="text-left py-2 px-2 font-medium text-gray-600">Modal</th>
                                    <th class="text-left py-2 px-2 font-medium text-gray-600">SKU</th>
                                    <th class="text-left py-2 px-2 font-medium text-gray-600 w-16">Aktif</th>
                                    <th class="text-left py-2 px-2 font-medium text-gray-600 w-16">Tersedia</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in bulkRows" :key="index">
                                    <tr class="border-b border-gray-100">
                                        <td class="py-1.5 px-2 text-gray-400 text-xs" x-text="index + 1"></td>
                                        <td class="py-1.5 px-1">
                                            <input type="text" :name="`products[${index}][name]`" class="input text-sm py-1.5" required x-model="row.name" placeholder="Nama produk">
                                        </td>
                                        <td class="py-1.5 px-1">
                                            <select :name="`products[${index}][category_id]`" class="input text-sm py-1.5" required x-model="row.category_id">
                                                <option value="">Pilih</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-1.5 px-1">
                                            <input type="number" :name="`products[${index}][price]`" class="input text-sm py-1.5" required min="0" step="1000" x-model="row.price" placeholder="0">
                                        </td>
                                        <td class="py-1.5 px-1">
                                            <input type="number" :name="`products[${index}][cost]`" class="input text-sm py-1.5" min="0" step="1000" x-model="row.cost" placeholder="0">
                                        </td>
                                        <td class="py-1.5 px-1">
                                            <input type="text" :name="`products[${index}][sku]`" class="input text-sm py-1.5" x-model="row.sku" placeholder="SKU">
                                        </td>
                                        <td class="py-1.5 px-1 text-center">
                                            <input type="checkbox" :name="`products[${index}][is_active]`" value="1" x-model="row.is_active" class="w-4 h-4 rounded">
                                        </td>
                                        <td class="py-1.5 px-1 text-center">
                                            <input type="checkbox" :name="`products[${index}][is_available]`" value="1" x-model="row.is_available" class="w-4 h-4 rounded">
                                        </td>
                                        <td class="py-1.5 px-1">
                                            <button type="button" @click="removeBulkRow(index)" class="text-red-400 hover:text-red-600" x-show="bulkRows.length > 1">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="flex items-center gap-3 mt-3">
                        <button type="button" @click="addBulkRow()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris
                        </button>
                        <button type="button" @click="addBulkRows(5)" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            <i class="fas fa-plus-circle mr-1"></i> +5 Baris
                        </button>
                        <button type="button" @click="addBulkRows(10)" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            <i class="fas fa-plus-circle mr-1"></i> +10 Baris
                        </button>
                        <span class="text-xs text-gray-400 ml-auto" x-text="bulkRows.length + ' baris'"></span>
                    </div>

                    <div class="flex gap-3 mt-6 pt-4 border-t">
                        <button type="submit" class="flex-1 text-white font-semibold py-2.5 px-4 rounded-lg shadow transition" style="background-color: #6366f1;">
                            <i class="fas fa-layer-group mr-2"></i> 
                            Simpan Semua (<span x-text="bulkRows.length"></span> produk)
                        </button>
                        <button type="button" @click="showBulkInsertModal = false" class="flex-1 btn-secondary">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Bulk Change Category -->
    <div x-show="showBulkCategoryModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="showBulkCategoryModal = false">
        <div class="bg-white rounded-lg max-w-md w-full" @click.stop>
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Ubah Kategori</h3>
                    <button @click="showBulkCategoryModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Pindahkan <span class="font-semibold text-indigo-600" x-text="selectedIds.length"></span> produk ke kategori baru:
                </p>
                <form action="{{ route('products.bulkUpdate') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="bulk_action" value="change_category">
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="product_ids[]" :value="id">
                    </template>
                    
                    <select name="bulk_category_id" class="input mb-4" required>
                        <option value="">Pilih Kategori Baru</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition">
                            <i class="fas fa-folder mr-2"></i> Ubah Kategori
                        </button>
                        <button type="button" @click="showBulkCategoryModal = false" class="flex-1 btn-secondary">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Bulk Adjust Price -->
    <div x-show="showBulkPriceModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="showBulkPriceModal = false">
        <div class="bg-white rounded-lg max-w-md w-full" @click.stop>
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Sesuaikan Harga</h3>
                    <button @click="showBulkPriceModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Sesuaikan harga untuk <span class="font-semibold text-indigo-600" x-text="selectedIds.length"></span> produk:
                </p>
                <form action="{{ route('products.bulkUpdate') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="bulk_action" value="adjust_price">
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="product_ids[]" :value="id">
                    </template>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Penyesuaian</label>
                            <select name="price_adjustment_type" class="input" required x-model="priceAdjType">
                                <option value="fixed">Nominal Tetap (Rp)</option>
                                <option value="percentage">Persentase (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nilai</label>
                            <input type="number" name="price_adjustment_value" class="input" required step="1" x-model="priceAdjValue">
                            <p class="text-xs text-gray-500 mt-1">
                                <template x-if="priceAdjType === 'fixed'">
                                    <span>Gunakan nilai positif untuk menaikkan, negatif untuk menurunkan. Misal: 5000 atau -2000</span>
                                </template>
                                <template x-if="priceAdjType === 'percentage'">
                                    <span>Gunakan nilai positif untuk menaikkan, negatif untuk menurunkan. Misal: 10 (naik 10%) atau -15 (turun 15%)</span>
                                </template>
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="flex-1 bg-purple-500 hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-lg transition">
                            <i class="fas fa-tag mr-2"></i> Sesuaikan Harga
                        </button>
                        <button type="button" @click="showBulkPriceModal = false" class="flex-1 btn-secondary">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Bulk Delete Confirm -->
    <div x-show="showBulkDeleteModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="showBulkDeleteModal = false">
        <div class="bg-white rounded-lg max-w-md w-full" @click.stop>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
                        <p class="text-sm text-gray-600">
                            Hapus <span class="font-semibold text-red-600" x-text="selectedIds.length"></span> produk yang dipilih?
                        </p>
                    </div>
                </div>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-red-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Aksi ini akan menghapus semua produk yang dipilih beserta gambarnya. Produk yang dihapus masih bisa dipulihkan dari database (soft delete).
                    </p>
                </div>

                <form action="{{ route('products.bulkDestroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="product_ids[]" :value="id">
                    </template>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg transition">
                            <i class="fas fa-trash mr-2"></i> Ya, Hapus Semua
                        </button>
                        <button type="button" @click="showBulkDeleteModal = false" class="flex-1 btn-secondary">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

<script>
function productManager() {
    return {
        // Existing modals
        showModal: false,
        showStockModal: false,
        editMode: false,
        
        // Bulk modals
        showBulkInsertModal: false,
        showBulkCategoryModal: false,
        showBulkPriceModal: false,
        showBulkDeleteModal: false,

        // Selection
        selectedIds: [],
        allProductIds: [
            @foreach($products as $p){{ $p->id }},@endforeach
        ],

        // Bulk price adjustment
        priceAdjType: 'fixed',
        priceAdjValue: 0,

        // Current product for single edit
        currentProduct: {
            id: null,
            category_id: '',
            name: '',
            description: '',
            price: '',
            cost: '',
            sku: '',
            image: null,
            is_active: true,
            is_available: true,
            is_favorite: false
        },

        // Stock product
        stockProduct: {
            id: null,
            name: '',
            category_name: '',
            quantity: 0,
            min_quantity: 0,
            unit: 'porsi'
        },

        // Bulk insert rows
        bulkRows: [
            {
                name: '',
                category_id: '',
                price: '',
                cost: '',
                sku: '',
                is_active: true,
                is_available: true
            }
        ],

        get allSelected() {
            return this.allProductIds.length > 0 && this.allProductIds.every(id => this.selectedIds.includes(id));
        },

        emptyBulkRow() {
            return {
                name: '',
                category_id: '',
                price: '',
                cost: '',
                sku: '',
                is_active: true,
                is_available: true
            };
        },

        // Selection methods
        toggleSelect(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx > -1) {
                this.selectedIds.splice(idx, 1);
            } else {
                this.selectedIds.push(id);
            }
        },

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedIds = [...this.allProductIds];
            } else {
                this.selectedIds = [];
            }
        },

        // Modal openers
        openCreateModal() {
            this.editMode = false;
            this.currentProduct = {
                id: null,
                category_id: '',
                name: '',
                description: '',
                price: '',
                cost: '',
                sku: '',
                image: null,
                is_active: true,
                is_available: true,
                is_favorite: false
            };
            this.showModal = true;
        },
        
        openEditModal(productId) {
            fetch(`/products/${productId}/edit`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(product => {
                    this.currentProduct = {
                        id: product.id,
                        category_id: product.category_id,
                        name: product.name,
                        description: product.description || '',
                        price: product.price,
                        cost: product.cost || '',
                        sku: product.sku || '',
                        image: product.image,
                        is_active: product.is_active ? true : false,
                        is_available: product.is_available ? true : false,
                        is_favorite: product.is_favorite ? true : false
                    };
                    this.editMode = true;
                    this.showModal = true;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat data produk: ' + error.message);
                });
        },
        
        openStockModal(productId) {
            fetch(`/products/${productId}/stock`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    this.stockProduct = {
                        id: data.product.id,
                        name: data.product.name,
                        category_name: data.product.category ? data.product.category.name : '',
                        quantity: data.inventory ? data.inventory.quantity : 0,
                        min_quantity: data.inventory ? data.inventory.min_quantity : 10,
                        unit: data.inventory ? data.inventory.unit : 'porsi'
                    };
                    this.showStockModal = true;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat data stok: ' + error.message);
                });
        },

        // Bulk Insert
        openBulkInsertModal() {
            this.bulkRows = [this.emptyBulkRow()];
            this.showBulkInsertModal = true;
        },

        addBulkRow() {
            this.bulkRows.push(this.emptyBulkRow());
        },

        addBulkRows(count) {
            for (let i = 0; i < count; i++) {
                this.bulkRows.push(this.emptyBulkRow());
            }
        },

        removeBulkRow(index) {
            this.bulkRows.splice(index, 1);
        },

        // Bulk Update actions
        bulkAction(action) {
            if (this.selectedIds.length === 0) return;

            // Create a form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("products.bulkUpdate") }}';
            
            // CSRF
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            // Method
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';
            form.appendChild(method);

            // Action
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'bulk_action';
            actionInput.value = action;
            form.appendChild(actionInput);

            // Product IDs
            this.selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'product_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        },

        openBulkCategoryModal() {
            this.showBulkCategoryModal = true;
        },

        openBulkPriceModal() {
            this.priceAdjType = 'fixed';
            this.priceAdjValue = 0;
            this.showBulkPriceModal = true;
        },

        // Bulk Delete
        confirmBulkDelete() {
            this.showBulkDeleteModal = true;
        }
    }
}
</script>
@endsection
