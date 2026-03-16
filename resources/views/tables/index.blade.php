@extends('layouts.app')

@section('title', 'Tables')
@section('header', 'Table Management')

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
                <h2 class="text-xl font-bold text-gray-900">All Tables</h2>
                <p class="text-gray-600 text-sm">Manage restaurant tables and seating</p>
            </div>
            <button @click="showModal = true" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Add Table
            </button>
        </div>
        
        <!-- Filters & Stats -->
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <input type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Search table number...">
            <select name="status" class="input">
                <option value="">All Status</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="cleaning" {{ request('status') === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
            </select>
            <button type="submit" class="btn-secondary">
                <i class="fas fa-filter mr-2"></i> Filter
            </button>
        </form>
        
        <!-- Status Summary -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 mb-1">Available</p>
                        <p class="text-2xl font-bold text-green-700">
                            {{ $tables->filter(fn($t) => $t->status->value === 'available')->count() }}
                        </p>
                    </div>
                    <i class="fas fa-check-circle text-3xl text-green-400"></i>
                </div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600 mb-1">Occupied</p>
                        <p class="text-2xl font-bold text-red-700">
                            {{ $tables->filter(fn($t) => $t->status->value === 'occupied')->count() }}
                        </p>
                    </div>
                    <i class="fas fa-users text-3xl text-red-400"></i>
                </div>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 mb-1">Reserved</p>
                        <p class="text-2xl font-bold text-blue-700">
                            {{ $tables->filter(fn($t) => $t->status->value === 'reserved')->count() }}
                        </p>
                    </div>
                    <i class="fas fa-bookmark text-3xl text-blue-400"></i>
                </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-yellow-600 mb-1">Cleaning</p>
                        <p class="text-2xl font-bold text-yellow-700">
                            {{ $tables->filter(fn($t) => $t->status->value === 'cleaning')->count() }}
                        </p>
                    </div>
                    <i class="fas fa-broom text-3xl text-yellow-400"></i>
                </div>
            </div>
        </div>
        
        <!-- Tables Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($tables as $table)
            @php
            $statusColors = [
                'available' => 'border-green-500 bg-green-50',
                'occupied' => 'border-red-500 bg-red-50',
                'reserved' => 'border-blue-500 bg-blue-50',
                'cleaning' => 'border-yellow-500 bg-yellow-50'
            ];
            $statusIcons = [
                'available' => 'fa-check-circle text-green-600',
                'occupied' => 'fa-users text-red-600',
                'reserved' => 'fa-bookmark text-blue-600',
                'cleaning' => 'fa-broom text-yellow-600'
            ];
            @endphp
            <div class="border-2 {{ $statusColors[$table->status->value] ?? 'border-gray-200' }} rounded-lg p-4 text-center">
                <i class="fas {{ $statusIcons[$table->status->value] ?? 'fa-chair' }} text-3xl mb-3"></i>
                <h4 class="font-bold text-gray-900 mb-1">Table {{ $table->number }}</h4>
                <p class="text-xs text-gray-600 mb-2">
                    <i class="fas fa-chair text-xs"></i> {{ $table->capacity }} seats
                </p>
                <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$table->status->value] ?? 'bg-gray-100' }} font-medium capitalize">
                    {{ $table->status->value }}
                </span>
                
                <div class="flex gap-1 mt-3">
                    <a href="{{ route('tables.edit', $table->id) }}" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-xs py-1.5 rounded transition text-center block">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('tables.destroy', $table->id) }}" method="POST" class="flex-1"
                          onsubmit="return confirm('Delete this table?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white text-xs py-1.5 rounded transition">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                <i class="fas fa-chair text-5xl mb-3 block text-gray-300"></i>
                No tables found
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($tables->hasPages())
        <div class="mt-6">
            {{ $tables->links() }}
        </div>
        @endif
    </div>
    
    <!-- Modal Add Table -->
    <div x-show="showModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="showModal = false">
        <div class="bg-white rounded-lg max-w-md w-full" @click.stop>
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Add New Table</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form action="{{ route('tables.store') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Table Number *</label>
                            <input type="text" name="number" class="input" placeholder="e.g., 1, A1, VIP-1" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Capacity (Seats) *</label>
                            <input type="number" name="capacity" class="input" min="1" value="4" required>
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
                            <i class="fas fa-save mr-2"></i> Create Table
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
