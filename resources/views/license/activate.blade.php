<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi License - POS Resto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">POS Resto</h1>
            <p class="text-gray-600">Sistem Kasir Restoran</p>
        </div>

        @if($currentLicense && $currentLicense->isValid())
            <!-- Already Activated -->
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-green-800">Aplikasi Sudah Aktif</p>
                        <p class="text-sm text-green-700">License key: {{ substr($currentLicense->license_key, 0, 19) }}...</p>
                    </div>
                </div>
            </div>

            <div class="space-y-3 mb-6">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Nama Customer:</span>
                    <span class="font-semibold">{{ $currentLicense->customer_name }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Tipe License:</span>
                    <span class="font-semibold uppercase">{{ $currentLicense->license_type }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Tanggal Aktif:</span>
                    <span class="font-semibold">{{ $currentLicense->start_date->format('d M Y') }}</span>
                </div>
                @if($currentLicense->expiry_date)
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Tanggal Kadaluarsa:</span>
                    <span class="font-semibold">{{ $currentLicense->expiry_date->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">Sisa Waktu:</span>
                    <span class="font-semibold text-blue-600">{{ $currentLicense->daysRemaining() }} hari</span>
                </div>
                @else
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">Masa Aktif:</span>
                    <span class="font-semibold text-green-600">Selamanya (Lifetime)</span>
                </div>
                @endif
            </div>

            <a href="{{ route('dashboard') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition duration-200">
                Lanjut ke Dashboard
            </a>
        @else
            <!-- Step 1: Connection Status to License Manager -->
            <div class="mb-6">
                @if($connectionCheck['connected'])
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-green-800">Terhubung ke License Manager</p>
                                <p class="text-sm text-green-700">{{ $connectionCheck['server_url'] }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-red-800">Tidak Terhubung ke License Manager</p>
                                <p class="text-sm text-red-700 mt-1">{{ $connectionCheck['message'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-700 font-semibold mb-2">Pastikan:</p>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Aplikasi License Manager sudah berjalan</li>
                            <li>• URL server License Manager benar: <code class="bg-gray-200 px-1 rounded text-xs">{{ $connectionCheck['server_url'] }}</code></li>
                            <li>• Koneksi jaringan antara POS dan server License Manager lancar</li>
                        </ul>
                    </div>

                    <a href="{{ route('license.activate') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition duration-200">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Coba Hubungkan Ulang
                    </a>
                @endif
            </div>

            <!-- Step 2: Activation Form (only if connected) -->
            @if($connectionCheck['connected'])
                <div class="border-t pt-6">
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <p class="font-semibold text-yellow-800">Aplikasi Belum Diaktivasi</p>
                        </div>
                    </div>

                    <p class="text-gray-600 text-center mb-6">
                        Silakan masukkan license key yang telah Anda terima dari License Manager untuk mengaktifkan aplikasi ini.
                    </p>

                    @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <p class="text-red-800">{{ session('error') }}</p>
                    </div>
                    @endif

                    <form action="{{ route('license.activate.process') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label for="license_key" class="block text-sm font-semibold text-gray-700 mb-2">
                                License Key <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="license_key" 
                                name="license_key" 
                                value="{{ old('license_key') }}"
                                placeholder="XXXX-XXXX-XXXX-XXXX"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
                                required
                                maxlength="19"
                            >
                            @error('license_key')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Pemilik/Bisnis <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="customer_name" 
                                name="customer_name" 
                                value="{{ old('customer_name') }}"
                                placeholder="Otomatis terisi dari License Manager"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50"
                                required
                                readonly
                            >
                            <p id="lookup_status" class="text-sm mt-1 hidden"></p>
                            @error('customer_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button 
                            type="submit" 
                            id="btn_activate"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            Aktivasi Sekarang
                        </button>
                    </form>

                    <div class="mt-6 pt-6 border-t">
                        <p class="text-sm text-gray-500 text-center">
                            Belum punya license key? 
                            <a href="https://wa.me/6281938527772?text=Halo%2C%20saya%20ingin%20menanyakan%20informasi%20mengenai%20license%20POS.%20Mohon%20informasinya.%20Terima%20kasih." target="_blank" class="text-blue-600 hover:underline font-semibold">Hubungi Kami</a>
                        </p>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <script>
        const licenseKeyInput = document.getElementById('license_key');
        const customerNameInput = document.getElementById('customer_name');
        const lookupStatus = document.getElementById('lookup_status');
        const btnActivate = document.getElementById('btn_activate');
        let lookupTimeout = null;

        // Auto-format license key input (add dashes) and trigger lookup
        licenseKeyInput?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^A-Z0-9]/gi, '').toUpperCase();
            let formatted = '';
            
            for (let i = 0; i < value.length && i < 16; i++) {
                if (i > 0 && i % 4 === 0) {
                    formatted += '-';
                }
                formatted += value[i];
            }
            
            e.target.value = formatted;
            
            // Reset when key changes
            if (customerNameInput) {
                customerNameInput.value = '';
                customerNameInput.classList.remove('bg-green-50', 'border-green-300');
                customerNameInput.classList.add('bg-gray-50');
            }
            if (btnActivate) btnActivate.disabled = true;
            
            // Lookup when key is complete (XXXX-XXXX-XXXX-XXXX = 19 chars)
            if (formatted.length === 19) {
                clearTimeout(lookupTimeout);
                lookupTimeout = setTimeout(() => lookupLicense(formatted), 300);
            } else {
                if (lookupStatus) {
                    lookupStatus.classList.add('hidden');
                }
            }
        });

        async function lookupLicense(key) {
            if (!lookupStatus || !customerNameInput) return;
            
            lookupStatus.classList.remove('hidden', 'text-green-600', 'text-red-500', 'text-blue-600');
            lookupStatus.classList.add('text-blue-600');
            lookupStatus.textContent = 'Mencari data license...';
            
            try {
                const response = await fetch('{{ route("license.lookup") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ license_key: key }),
                });
                
                const data = await response.json();
                
                if (data.found && data.data) {
                    const name = data.data.customer_name || data.data.business_name || '';
                    customerNameInput.value = name;
                    customerNameInput.classList.remove('bg-gray-50');
                    customerNameInput.classList.add('bg-green-50', 'border-green-300');
                    
                    lookupStatus.classList.remove('text-blue-600', 'text-red-500');
                    lookupStatus.classList.add('text-green-600');
                    
                    let info = [];
                    if (data.data.license_type) info.push('Tipe: ' + data.data.license_type.toUpperCase());
                    if (data.data.status) info.push('Status: ' + data.data.status);
                    if (data.data.business_name && data.data.customer_name) info.push('Bisnis: ' + data.data.business_name);
                    lookupStatus.textContent = '✓ License ditemukan' + (info.length ? ' — ' + info.join(', ') : '');
                    
                    btnActivate.disabled = false;
                } else {
                    lookupStatus.classList.remove('text-blue-600', 'text-green-600');
                    lookupStatus.classList.add('text-red-500');
                    lookupStatus.textContent = '✗ ' + (data.message || 'License key tidak ditemukan di License Manager.');
                    btnActivate.disabled = true;
                }
            } catch (err) {
                lookupStatus.classList.remove('text-blue-600', 'text-green-600');
                lookupStatus.classList.add('text-red-500');
                lookupStatus.textContent = '✗ Gagal menghubungi server.';
                btnActivate.disabled = true;
            }
        }
    </script>
</body>
</html>
