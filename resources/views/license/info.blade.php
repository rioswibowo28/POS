@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
                Informasi License
            </h1>
        </div>

        <!-- License Status Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Status Banner -->
            @if($license->isValid())
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <div class="flex items-center text-white">
                        <svg class="h-8 w-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h2 class="text-xl font-bold">License Aktif</h2>
                            <p class="text-green-100 text-sm">Aplikasi berjalan normal</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                    <div class="flex items-center text-white">
                        <svg class="h-8 w-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h2 class="text-xl font-bold">License Tidak Aktif</h2>
                            <p class="text-red-100 text-sm">Segera perpanjang license Anda</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- License Details -->
            <div class="px-6 py-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- License Key -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">License Key</p>
                        <p class="font-mono text-sm font-semibold text-gray-800">{{ $license->license_key }}</p>
                    </div>

                    <!-- License Type -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Tipe License</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                            @if($license->license_type === 'lifetime') bg-purple-100 text-purple-800
                            @elseif($license->license_type === 'yearly') bg-blue-100 text-blue-800
                            @elseif($license->license_type === 'monthly') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ strtoupper($license->license_type) }}
                        </span>
                    </div>

                    <!-- Customer Name -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Nama Customer</p>
                        <p class="font-semibold text-gray-800">{{ $license->customer_name }}</p>
                    </div>

                    <!-- Business Name -->
                    @if($license->business_name)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Nama Bisnis</p>
                        <p class="font-semibold text-gray-800">{{ $license->business_name }}</p>
                    </div>
                    @endif

                    <!-- Start Date -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Tanggal Aktif</p>
                        <p class="font-semibold text-gray-800">{{ $license->start_date->format('d F Y') }}</p>
                    </div>

                    <!-- Expiry Date -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Tanggal Kadaluarsa</p>
                        @if($license->expiry_date)
                            <p class="font-semibold text-gray-800">{{ $license->expiry_date->format('d F Y') }}</p>
                        @else
                            <p class="font-semibold text-green-600">Selamanya (Lifetime)</p>
                        @endif
                    </div>
                </div>

                <!-- Expiry Warning -->
                @if($license->expiry_date)
                <div class="mt-6 pt-6 border-t">
                    @if($license->isExpiringSoon())
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                            <div class="flex items-center">
                                <svg class="h-6 w-6 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-yellow-800">Peringatan!</p>
                                    <p class="text-sm text-yellow-700">License akan kadaluarsa dalam <strong>{{ $license->daysRemaining() }} hari</strong>. Segera hubungi kami untuk perpanjangan.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                            <div class="flex items-center">
                                <svg class="h-6 w-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-blue-800">Sisa Masa Aktif</p>
                                    <p class="text-sm text-blue-700">License aktif selama <strong>{{ $license->daysRemaining() }} hari</strong> lagi.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                @endif

                <!-- Limits -->
                @if($license->max_users || $license->max_tables)
                <div class="mt-6 pt-6 border-t">
                    <h3 class="font-semibold text-gray-800 mb-3">Batasan License</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @if($license->max_users)
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="text-gray-700">Max Users: <strong>{{ $license->max_users }}</strong></span>
                        </div>
                        @endif

                        @if($license->max_tables)
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-gray-700">Max Tables: <strong>{{ $license->max_tables }}</strong></span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Contact Support -->
                <div class="mt-6 pt-6 border-t">
                    <p class="text-sm text-gray-600 text-center">
                        Butuh bantuan atau ingin perpanjang license? 
                        <a href="https://wa.me/6281938527772?text=Halo%2C%20saya%20ingin%20menanyakan%20informasi%20mengenai%20license%20POS.%20Mohon%20informasinya.%20Terima%20kasih." target="_blank" class="text-blue-600 hover:underline font-semibold">Hubungi Support</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Update/Renew License Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-6 mb-6" x-data="{ checking: false, verified: false, errorMsg: '', warningMsg: '', lookupInfo: '' }">
            <div class="px-6 py-4 border-b bg-gray-50 flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <h3 class="font-semibold text-gray-800">Perbarui License</h3>
            </div>

            <div class="px-6 py-5">
                @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
                    <p class="text-green-800 text-sm">{{ session('success') }}</p>
                </div>
                @endif

                @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                    <p class="text-red-800 text-sm">{{ session('error') }}</p>
                </div>
                @endif

                <p class="text-sm text-gray-600 mb-4">
                    Masukkan license key baru, lalu cek ke License Manager. Jika valid, Anda dapat memperbarui license.
                </p>

                <form action="{{ route('license.update') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="new_license_key" class="block text-sm font-semibold text-gray-700 mb-2">
                            License Key <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="new_license_key" 
                            name="license_key" 
                            value="{{ old('license_key') }}"
                            placeholder="XXXX-XXXX-XXXX-XXXX"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
                            required
                            maxlength="19"
                            :disabled="checking"
                        >
                        @error('license_key')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lookup Status -->
                    <div x-show="lookupInfo" x-transition>
                        <div class="bg-green-50 border-l-4 border-green-500 p-3">
                            <p class="text-green-800 text-sm flex items-center">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span x-text="lookupInfo"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Warning Status -->
                    <div x-show="warningMsg" x-transition>
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3">
                            <p class="text-yellow-800 text-sm flex items-center">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span x-text="warningMsg"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Error Status -->
                    <div x-show="errorMsg" x-transition>
                        <div class="bg-red-50 border-l-4 border-red-500 p-3">
                            <p class="text-red-800 text-sm flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="errorMsg"></span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <!-- Cek License Button -->
                        <button 
                            type="button" 
                            id="btn_check"
                            @click="
                                const key = document.getElementById('new_license_key').value.trim();
                                if (key.length < 19) { errorMsg = 'Masukkan license key lengkap (format: XXXX-XXXX-XXXX-XXXX).'; lookupInfo = ''; warningMsg = ''; verified = false; return; }
                                if (key === '{{ $license->license_key }}') { warningMsg = 'License key ini sedang digunakan saat ini.'; errorMsg = ''; lookupInfo = ''; verified = false; return; }
                                checking = true;
                                errorMsg = '';
                                warningMsg = '';
                                lookupInfo = '';
                                verified = false;
                                fetch('{{ route('license.lookup') }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: JSON.stringify({ license_key: key })
                                })
                                .then(r => r.json())
                                .then(data => {
                                    checking = false;
                                    if (data.found && data.data) {
                                        const status = (data.data.status || '').toLowerCase();
                                        if (status === 'active') {
                                            errorMsg = 'License key ini sudah aktif/digunakan. Tidak dapat digunakan lagi.';
                                            verified = false;
                                            return;
                                        }
                                        if (status === 'expired') {
                                            errorMsg = 'License key ini sudah kadaluarsa. Tidak dapat digunakan.';
                                            verified = false;
                                            return;
                                        }
                                        verified = true;
                                        let info = 'License ditemukan';
                                        if (data.data.customer_name) info += ' — ' + data.data.customer_name;
                                        if (data.data.license_type) info += ' (' + data.data.license_type + ')';
                                        if (status) info += ' [' + status + ']';
                                        lookupInfo = info;
                                    } else {
                                        errorMsg = 'License tidak ditemukan di server. Periksa kembali license key.';
                                    }
                                })
                                .catch(() => {
                                    checking = false;
                                    errorMsg = 'Gagal terhubung ke License Manager. Pastikan koneksi internet aktif.';
                                });
                            "
                            :disabled="checking"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium text-sm py-2 px-3 rounded-lg transition duration-200 flex items-center justify-center disabled:opacity-50"
                        >
                            <template x-if="!checking">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Cek License
                                </span>
                            </template>
                            <template x-if="checking">
                                <span class="flex items-center">
                                    <svg class="animate-spin w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Mengecek...
                                </span>
                            </template>
                        </button>

                        <!-- Perbarui License Button (only enabled after check) -->
                        <button 
                            type="submit" 
                            id="btn_renew"
                            :disabled="!verified"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm py-2 px-3 rounded-lg transition duration-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Perbarui License
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const keyInput = document.getElementById('new_license_key');
    if (!keyInput) return;

    // Auto-format license key: XXXX-XXXX-XXXX-XXXX
    keyInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        if (value.length > 16) value = value.substring(0, 16);
        
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) formatted += '-';
            formatted += value[i];
        }
        e.target.value = formatted;
    });
});
</script>
@endpush

@endsection
