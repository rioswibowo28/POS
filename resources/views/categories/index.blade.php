@extends('layouts.app')

@section('title', 'Categories')
@section('header', 'Category Management')

@section('content')
<div x-data="{ showModal: false }">
    
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    <div class="card">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">All Categories</h2>
                <p class="text-gray-600 text-sm">Organize products into categories</p>
            </div>
            <button @click="showModal = true" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Add Category
            </button>
        </div>
        
        <!-- Filters -->
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <input type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Search categories...">
            <select name="is_active" class="input">
                <option value="">All Status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn-secondary">
                <i class="fas fa-filter mr-2"></i> Filter
            </button>
        </form>
        
        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($categories as $category)
            <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-primary-500 transition">
                <div class="w-20 h-20 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    @if($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover rounded-full">
                    @else
                    <i class="fas fa-tag text-3xl text-white"></i>
                    @endif
                </div>
                
                <h4 class="font-semibold text-gray-900 text-center mb-2">{{ $category->name }}</h4>
                
                @if($category->description)
                <p class="text-xs text-gray-500 text-center mb-3">{{ Str::limit($category->description, 50) }}</p>
                @endif
                
                <div class="flex gap-2 justify-center mb-4">
                    @if($category->is_active)
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactive</span>
                    @endif
                    
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                        {{ $category->products_count }} Products
                    </span>
                </div>
                
                <div class="flex gap-2">
                    <button onclick="alert('Edit feature coming soon')" class="flex-1 btn-secondary text-sm py-2">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="flex-1"
                          onsubmit="return confirm('Delete this category? All products will be affected.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white text-sm py-2 rounded-lg transition">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                <i class="fas fa-folder-open text-5xl mb-3 block text-gray-300"></i>
                No categories found
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($categories->hasPages())
        <div class="mt-6">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
    
    <!-- Modal Add Category -->
    <div x-show="showModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="showModal = false">
        <div class="bg-white rounded-lg max-w-md w-full" @click.stop>
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Add New Category</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                            <input type="text" name="name" class="input" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="3" class="input"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category Image</label>
                            <input type="file" name="image" accept="image/*" class="input">
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" checked class="mr-2">
                                <span class="text-sm">Active</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="flex-1 btn-primary">
                            <i class="fas fa-save mr-2"></i> Create Category
                        </button>
                        <button type="button" @click="showModal = false" class="flex-1 btn-secondary">
                            Cancel
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
@endsection
