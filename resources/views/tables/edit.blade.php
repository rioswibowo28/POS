@extends('layouts.app')

@section('title', 'Edit Table')
@section('header', 'Edit Table #' . $table->number)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <div class="mb-6">
            <a href="{{ route('tables.index') }}" class="text-primary-600 hover:text-primary-700 text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Back to Tables
            </a>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('tables.update', $table->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Table Number -->
                <div>
                    <label for="number" class="block text-sm font-medium text-gray-700 mb-2">
                        Table Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="number" 
                           name="number" 
                           value="{{ old('number', $table->number) }}"
                           class="input @error('number') border-red-500 @enderror" 
                           placeholder="e.g., 1, A1, VIP-1"
                           required>
                    @error('number')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Unique identifier for this table</p>
                </div>

                <!-- Capacity -->
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                        Capacity (Seats) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="capacity" 
                           name="capacity" 
                           value="{{ old('capacity', $table->capacity) }}"
                           class="input @error('capacity') border-red-500 @enderror" 
                           min="1"
                           required>
                    @error('capacity')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Maximum number of people this table can accommodate</p>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" 
                            name="status" 
                            class="input @error('status') border-red-500 @enderror"
                            required>
                        <option value="available" {{ old('status', $table->status->value) === 'available' ? 'selected' : '' }}>
                            Available
                        </option>
                        <option value="occupied" {{ old('status', $table->status->value) === 'occupied' ? 'selected' : '' }}>
                            Occupied
                        </option>
                        <option value="reserved" {{ old('status', $table->status->value) === 'reserved' ? 'selected' : '' }}>
                            Reserved
                        </option>
                        <option value="cleaning" {{ old('status', $table->status->value) === 'cleaning' ? 'selected' : '' }}>
                            Cleaning
                        </option>
                    </select>
                    @error('status')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Current status of the table</p>
                </div>

                <!-- Active Status -->
                <div>
                    <label class="flex items-start">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $table->is_active) ? 'checked' : '' }}
                               class="mt-1 mr-3">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Active</span>
                            <p class="text-xs text-gray-500">Inactive tables won't be available for new orders</p>
                        </div>
                    </label>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 text-lg mr-3 mt-1"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Note:</p>
                            <ul class="space-y-1">
                                <li>• Table number must be unique across all tables</li>
                                <li>• Status can be changed manually or will update automatically based on orders</li>
                                <li>• Inactive tables will not appear in POS table selection</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 mt-8">
                <button type="submit" class="flex-1 btn-primary">
                    <i class="fas fa-save mr-2"></i> Update Table
                </button>
                <a href="{{ route('tables.index') }}" class="flex-1 btn-secondary text-center">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>

        <!-- Delete Section -->
        @if(!$table->orders()->whereIn('status', ['pending', 'processing'])->exists())
        <div class="border-t mt-8 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Danger Zone</h3>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium text-red-900">Delete This Table</p>
                        <p class="text-sm text-red-700 mt-1">Once deleted, this table cannot be recovered.</p>
                    </div>
                    <form action="{{ route('tables.destroy', $table->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete Table {{ $table->number }}? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-trash mr-2"></i> Delete Table
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @else
        <div class="border-t mt-8 pt-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-lg mr-3 mt-1"></i>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold">Cannot Delete Table</p>
                        <p class="mt-1">This table has active orders and cannot be deleted. Complete or cancel all orders first.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
