@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Buat License Baru</h1>
            <p class="text-gray-600 mt-1">Generate license key baru untuk customer</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <form action="{{ route('license.store') }}" method="POST">
                @csrf

                <!-- License Type -->
                <div class="mb-6">
                    <label for="license_type" class="block text-sm font-semibold text-gray-700 mb-2">
                        Tipe License <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="license_type" 
                        name="license_type" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                        <option value="">-- Pilih Tipe --</option>
                        <option value="trial" {{ old('license_type') === 'trial' ? 'selected' : '' }}>Trial (14 hari)</option>
                        <option value="monthly" {{ old('license_type') === 'monthly' ? 'selected' : '' }}>Monthly (1 bulan)</option>
                        <option value="yearly" {{ old('license_type') === 'yearly' ? 'selected' : '' }}>Yearly (1 tahun)</option>
                        <option value="lifetime" {{ old('license_type') === 'lifetime' ? 'selected' : '' }}>Lifetime (selamanya)</option>
                    </select>
                    @error('license_type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Customer Information -->
                <div class="border-t pt-6 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Informasi Customer (Opsional)</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Customer
                            </label>
                            <input 
                                type="text" 
                                id="customer_name" 
                                name="customer_name" 
                                value="{{ old('customer_name') }}"
                                placeholder="Nama pemilik atau PIC"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('customer_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="business_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Bisnis/Restoran
                            </label>
                            <input 
                                type="text" 
                                id="business_name" 
                                name="business_name" 
                                value="{{ old('business_name') }}"
                                placeholder="Nama restoran atau bisnis"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('business_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="customer_email" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email
                                </label>
                                <input 
                                    type="email" 
                                    id="customer_email" 
                                    name="customer_email" 
                                    value="{{ old('customer_email') }}"
                                    placeholder="customer@example.com"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                @error('customer_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    No. Telepon
                                </label>
                                <input 
                                    type="text" 
                                    id="customer_phone" 
                                    name="customer_phone" 
                                    value="{{ old('customer_phone') }}"
                                    placeholder="08123456789"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                @error('customer_phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- License Limits -->
                <div class="border-t pt-6 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Batasan License (Opsional)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="max_users" class="block text-sm font-semibold text-gray-700 mb-2">
                                Maksimal User
                            </label>
                            <input 
                                type="number" 
                                id="max_users" 
                                name="max_users" 
                                value="{{ old('max_users') }}"
                                placeholder="Unlimited jika kosong"
                                min="1"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('max_users')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_tables" class="block text-sm font-semibold text-gray-700 mb-2">
                                Maksimal Meja
                            </label>
                            <input 
                                type="number" 
                                id="max_tables" 
                                name="max_tables" 
                                value="{{ old('max_tables') }}"
                                placeholder="Unlimited jika kosong"
                                min="1"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('max_tables')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                        Catatan
                    </label>
                    <textarea 
                        id="notes" 
                        name="notes" 
                        rows="3"
                        placeholder="Catatan internal tentang license ini..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex justify-between pt-6 border-t">
                    <a href="{{ route('license.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button 
                        type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Generate License
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
