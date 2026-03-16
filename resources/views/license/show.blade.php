@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Detail License</h1>
            <div class="flex space-x-2">
                @if($license->status === 'active' || $license->status === 'expired')
                <button 
                    onclick="document.getElementById('renewModal').classList.remove('hidden')"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Perpanjang
                </button>
                @endif

                @if($license->status === 'active')
                <form action="{{ route('license.suspend', $license->id) }}" method="POST" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        onclick="return confirm('Suspend license ini?')"
                        class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Suspend
                    </button>
                </form>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
        @endif

        <!-- License Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Status Banner -->
            <div class="px-6 py-4
                @if($license->status === 'active') bg-gradient-to-r from-green-500 to-green-600
                @elseif($license->status === 'trial') bg-gradient-to-r from-blue-500 to-blue-600
                @elseif($license->status === 'expired') bg-gradient-to-r from-red-500 to-red-600
                @else bg-gradient-to-r from-orange-500 to-orange-600
                @endif">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <div>
                            <h2 class="text-xl font-bold">{{ strtoupper($license->status) }}</h2>
                            <p class="text-white text-sm opacity-90">{{ strtoupper($license->license_type) }} License</p>
                        </div>
                    </div>
                    @if($license->isExpiringSoon())
                    <div class="bg-white bg-opacity-20 rounded-lg px-3 py-1">
                        <p class="text-sm font-semibold">{{ $license->daysRemaining() }} hari lagi</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- License Key -->
            <div class="px-6 py-6 bg-gray-50 border-b">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">License Key</p>
                        <p class="font-mono text-2xl font-bold text-gray-800">{{ $license->license_key }}</p>
                    </div>
                    <button 
                        onclick="copyLicenseKey()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Copy
                    </button>
                </div>
            </div>

            <!-- Details -->
            <div class="px-6 py-6">
                <h3 class="font-semibold text-gray-800 mb-4 text-lg">Informasi Customer</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Nama Customer</p>
                        <p class="font-semibold text-gray-800">{{ $license->customer_name ?? '-' }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Nama Bisnis</p>
                        <p class="font-semibold text-gray-800">{{ $license->business_name ?? '-' }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Email</p>
                        <p class="font-semibold text-gray-800">{{ $license->customer_email ?? '-' }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">No. Telepon</p>
                        <p class="font-semibold text-gray-800">{{ $license->customer_phone ?? '-' }}</p>
                    </div>
                </div>

                <h3 class="font-semibold text-gray-800 mb-4 text-lg border-t pt-6">Informasi License</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Tanggal Dibuat</p>
                        <p class="font-semibold text-gray-800">{{ $license->created_at->format('d F Y H:i') }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Tanggal Aktivasi</p>
                        <p class="font-semibold text-gray-800">
                            {{ $license->activated_at ? $license->activated_at->format('d F Y H:i') : 'Belum diaktivasi' }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Tanggal Mulai</p>
                        <p class="font-semibold text-gray-800">
                            {{ $license->start_date ? $license->start_date->format('d F Y') : '-' }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Tanggal Kadaluarsa</p>
                        @if($license->expiry_date)
                            <p class="font-semibold text-gray-800">{{ $license->expiry_date->format('d F Y') }}</p>
                            @if(!$license->expiry_date->isPast())
                                <p class="text-xs {{ $license->isExpiringSoon() ? 'text-yellow-600' : 'text-gray-500' }} mt-1">
                                    {{ $license->daysRemaining() }} hari lagi
                                </p>
                            @endif
                        @else
                            <p class="font-semibold text-green-600">Selamanya (Lifetime)</p>
                        @endif
                    </div>

                    @if($license->activated_by_ip)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">IP Aktivasi</p>
                        <p class="font-mono text-sm font-semibold text-gray-800">{{ $license->activated_by_ip }}</p>
                    </div>
                    @endif

                    @if($license->last_checked_at)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Terakhir Dicek</p>
                        <p class="font-semibold text-gray-800">{{ $license->last_checked_at->diffForHumans() }}</p>
                    </div>
                    @endif
                </div>

                <!-- Limits -->
                @if($license->max_users || $license->max_tables)
                <h3 class="font-semibold text-gray-800 mb-4 text-lg border-t pt-6">Batasan</h3>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    @if($license->max_users)
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-600">Maksimal User</p>
                                <p class="font-bold text-2xl text-blue-600">{{ $license->max_users }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($license->max_tables)
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-600">Maksimal Meja</p>
                                <p class="font-bold text-2xl text-green-600">{{ $license->max_tables }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Notes -->
                @if($license->notes)
                <div class="border-t pt-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Catatan</h3>
                    <p class="text-gray-600 bg-gray-50 rounded-lg p-4">{{ $license->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ route('license.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar License
            </a>
        </div>
    </div>
</div>

<!-- Renew Modal -->
<div id="renewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Perpanjang License</h3>
        
        <form action="{{ route('license.renew', $license->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="renew_license_type" class="block text-sm font-semibold text-gray-700 mb-2">
                    Perpanjang untuk
                </label>
                <select 
                    id="renew_license_type" 
                    name="license_type" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    required
                >
                    <option value="monthly">1 Bulan</option>
                    <option value="yearly">1 Tahun</option>
                    <option value="lifetime">Lifetime</option>
                </select>
            </div>

            <div class="flex justify-between">
                <button 
                    type="button"
                    onclick="document.getElementById('renewModal').classList.add('hidden')"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg"
                >
                    Perpanjang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function copyLicenseKey() {
    const key = "{{ $license->license_key }}";
    navigator.clipboard.writeText(key).then(() => {
        alert('License key berhasil disalin!');
    });
}
</script>
@endsection
