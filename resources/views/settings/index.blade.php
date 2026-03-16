@extends('layouts.app')

@section('title', 'Settings')
@section('header', 'Restaurant Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    
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
    
    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <div class="font-medium mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Validation Errors:</div>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Restaurant Information</h2>
            <p class="text-gray-600 text-sm">Configure your restaurant details and preferences</p>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="space-y-6">
                <!-- Restaurant Info Section -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-store text-primary-600 mr-2"></i>
                        Restaurant Details
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Restaurant Name *</label>
                            <input type="text" name="restaurant_name" value="{{ old('restaurant_name', $settings['restaurant_name'] ?? '') }}" class="input" required>
                            @error('restaurant_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Restaurant Logo</label>
                            <input type="file" name="restaurant_logo" accept="image/png,image/jpg,image/jpeg,image/svg+xml" class="input" onchange="previewLogo(event)">
                            <p class="text-xs text-gray-500 mt-1">Recommended: PNG, JPG, SVG. Max 5MB. Used for favicon, receipts, etc.</p>
                            @error('restaurant_logo')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            
                            <!-- Logo Preview -->
                            <div class="mt-3 flex items-center gap-4">
                                @if(isset($settings['restaurant_logo']) && $settings['restaurant_logo'])
                                <div id="current-logo-container" class="flex items-center gap-2">
                                    <div class="w-20 h-20 border-2 border-gray-200 rounded-lg p-2 bg-white">
                                        <img id="current-logo" src="{{ asset('storage/' . $settings['restaurant_logo']) }}" alt="Current Logo" class="w-full h-full object-contain">
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600 font-medium">Current Logo</p>
                                        <p class="text-xs text-gray-400">{{ basename($settings['restaurant_logo']) }}</p>
                                        <button type="button" 
                                                onclick="removeLogo()" 
                                                class="mt-1 text-xs text-red-600 hover:text-red-800 hover:underline">
                                            <i class="fas fa-times-circle mr-1"></i>Remove Logo
                                        </button>
                                    </div>
                                </div>
                                @endif
                                
                                <div id="new-logo-preview" class="hidden">
                                    <div class="flex items-center gap-2">
                                        <div class="w-20 h-20 border-2 border-primary-200 rounded-lg p-2 bg-primary-50">
                                            <img id="preview-image" src="" alt="New Logo" class="w-full h-full object-contain">
                                        </div>
                                        <div>
                                            <p class="text-xs text-primary-600 font-medium">New Logo Preview</p>
                                            <p id="preview-filename" class="text-xs text-gray-400"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                            <textarea name="restaurant_address" rows="3" class="input" required>{{ old('restaurant_address', $settings['restaurant_address'] ?? '') }}</textarea>
                            @error('restaurant_address')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="text" name="restaurant_phone" value="{{ old('restaurant_phone', $settings['restaurant_phone'] ?? '') }}" class="input" required>
                            @error('restaurant_phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" name="restaurant_email" value="{{ old('restaurant_email', $settings['restaurant_email'] ?? '') }}" class="input" required>
                            @error('restaurant_email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Pricing Section -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-percentage text-primary-600 mr-2"></i>
                        Pricing Settings
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tax Percentage *</label>
                            <div class="relative">
                                <input type="number" name="tax_percentage" value="{{ old('tax_percentage', $settings['tax_percentage'] ?? '10') }}" class="input pr-8" min="0" max="100" step="0.01" required>
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">%</span>
                            </div>
                            @error('tax_percentage')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Perhitungan PPN *</label>
                            <select name="tax_type" class="input" required>
                                <option value="exclude" {{ (old('tax_type', $settings['tax_type'] ?? 'exclude') === 'exclude') ? 'selected' : '' }}>Exclude (Harga belum termasuk PPN)</option>
                                <option value="include" {{ (old('tax_type', $settings['tax_type'] ?? 'exclude') === 'include') ? 'selected' : '' }}>Include (Harga sudah termasuk PPN)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Exclude: PPN ditambahkan di atas harga. Include: PPN sudah termasuk dalam harga.</p>
                            @error('tax_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Service Charge *</label>
                            <div class="relative">
                                <input type="number" name="service_charge" value="{{ old('service_charge', $settings['service_charge'] ?? '5') }}" class="input pr-8" min="0" max="100" step="0.01" required>
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">%</span>
                            </div>
                            @error('service_charge')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Currency *</label>
                            <select name="currency" class="input" required>
                                <option value="IDR" {{ (old('currency', $settings['currency'] ?? '') === 'IDR') ? 'selected' : '' }}>IDR - Indonesian Rupiah</option>
                                <option value="USD" {{ (old('currency', $settings['currency'] ?? '') === 'USD') ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="EUR" {{ (old('currency', $settings['currency'] ?? '') === 'EUR') ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="MYR" {{ (old('currency', $settings['currency'] ?? '') === 'MYR') ? 'selected' : '' }}>MYR - Malaysian Ringgit</option>
                            </select>
                            @error('currency')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Midtrans Payment Gateway Section -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-credit-card text-primary-600 mr-2"></i>
                        Midtrans Payment Gateway
                    </h3>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-yellow-600 mt-0.5"></i>
                            <div class="text-sm text-yellow-800">
                                <p class="font-medium mb-1">Payment Gateway Integration</p>
                                <p class="text-yellow-700">Enable Midtrans integration for automated QRIS/E-Wallet payments. If disabled, all digital payments will be recorded manually without real-time verification.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="flex items-center p-4 bg-gray-50 rounded-lg border-2 border-gray-200 hover:border-primary-300 cursor-pointer transition">
                                <input type="checkbox" 
                                       name="midtrans_enabled" 
                                       id="midtrans_enabled"
                                       value="1" 
                                       {{ old('midtrans_enabled', $settings['midtrans_enabled'] ?? '0') == '1' ? 'checked' : '' }} 
                                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5"
                                       onchange="toggleMidtransFields(this.checked)">
                                <div class="ml-3">
                                    <span class="text-sm font-semibold text-gray-900">Enable Midtrans Payment Gateway</span>
                                    <p class="text-xs text-gray-600 mt-1">Activate automated payment processing via Midtrans (QRIS, E-Wallet, etc.)</p>
                                </div>
                            </label>
                        </div>
                        
                        <div id="midtrans-credentials" class="space-y-4 {{ old('midtrans_enabled', $settings['midtrans_enabled'] ?? '0') == '1' ? '' : 'hidden opacity-50' }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Merchant ID</label>
                            <input type="text" name="midtrans_merchant_id" value="{{ old('midtrans_merchant_id', $settings['midtrans_merchant_id'] ?? '') }}" class="input" placeholder="e.g., G123456789">
                            <p class="text-xs text-gray-500 mt-1">Your Midtrans Merchant ID</p>
                            @error('midtrans_merchant_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Client Key</label>
                            <input type="text" name="midtrans_client_key" value="{{ old('midtrans_client_key', $settings['midtrans_client_key'] ?? '') }}" class="input" placeholder="e.g., SB-Mid-client-xxxxx">
                            <p class="text-xs text-gray-500 mt-1">Your Midtrans Client Key (for frontend)</p>
                            @error('midtrans_client_key')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Server Key</label>
                            <input type="password" name="midtrans_server_key" value="{{ old('midtrans_server_key', $settings['midtrans_server_key'] ?? '') }}" class="input" placeholder="e.g., SB-Mid-server-xxxxx">
                            <p class="text-xs text-gray-500 mt-1">Your Midtrans Server Key (keep it secret)</p>
                            @error('midtrans_server_key')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="midtrans_is_production" value="1" {{ old('midtrans_is_production', $settings['midtrans_is_production'] ?? '0') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Use Production Mode</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Uncheck for Sandbox/Testing mode</p>
                        </div>
                        </div>
                        
                        <div id="midtrans-disabled-notice" class="p-4 bg-gray-100 border border-gray-300 rounded-lg {{ old('midtrans_enabled', $settings['midtrans_enabled'] ?? '0') == '1' ? 'hidden' : '' }}">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-info-circle mr-2 text-gray-500"></i>
                                <strong>Midtrans Disabled:</strong> Digital payments (QRIS, E-Wallet) will be recorded manually without real-time verification.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- License API Section -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-key text-primary-600 mr-2"></i>
                        License Manager API Settings
                    </h3>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium mb-1">License Manager Integration</p>
                                <p class="text-blue-700">Configure API connection to the License Manager server. These settings allow the application to verify and validate licenses automatically.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                License Server URL *
                                <span class="text-xs text-gray-500 font-normal">(License Manager API Endpoint)</span>
                            </label>
                            <input type="url" 
                                   name="license_server_url" 
                                   value="{{ old('license_server_url', $settings['license_server_url'] ?? config('license.server_url')) }}" 
                                   class="input" 
                                   placeholder="e.g., https://license.yourdomain.com"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">The base URL of your License Manager server (without trailing slash)</p>
                            @error('license_server_url')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                License Key *
                                <span class="text-xs text-gray-500 font-normal">(Your Application License)</span>
                            </label>
                            <input type="text" 
                                   name="license_key" 
                                   value="{{ old('license_key', $settings['license_key'] ?? config('license.license_key')) }}" 
                                   class="input font-mono" 
                                   placeholder="XXXX-XXXX-XXXX-XXXX"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Your unique license key provided by the License Manager</p>
                            @error('license_key')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                License Check Interval
                                <span class="text-xs text-gray-500 font-normal">(in seconds)</span>
                            </label>
                            <select name="license_check_interval" class="input">
                                <option value="3600" {{ (old('license_check_interval', $settings['license_check_interval'] ?? config('license.check_interval')) == 3600) ? 'selected' : '' }}>1 Hour (3600s)</option>
                                <option value="21600" {{ (old('license_check_interval', $settings['license_check_interval'] ?? config('license.check_interval')) == 21600) ? 'selected' : '' }}>6 Hours (21600s)</option>
                                <option value="43200" {{ (old('license_check_interval', $settings['license_check_interval'] ?? config('license.check_interval')) == 43200) ? 'selected' : '' }}>12 Hours (43200s)</option>
                                <option value="86400" {{ (old('license_check_interval', $settings['license_check_interval'] ?? config('license.check_interval')) == 86400) ? 'selected' : '' }}>24 Hours (86400s) - Recommended</option>
                                <option value="172800" {{ (old('license_check_interval', $settings['license_check_interval'] ?? config('license.check_interval')) == 172800) ? 'selected' : '' }}>48 Hours (172800s)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">How often the system should verify license with the server</p>
                            @error('license_check_interval')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Grace Period After Expiry
                                <span class="text-xs text-gray-500 font-normal">(in days)</span>
                            </label>
                            <input type="number" 
                                   name="license_grace_period" 
                                   value="{{ old('license_grace_period', $settings['license_grace_period'] ?? config('license.grace_period', 7)) }}" 
                                   class="input" 
                                   min="0" 
                                   max="30"
                                   placeholder="7">
                            <p class="text-xs text-gray-500 mt-1">Number of days the application can run after license expiration</p>
                            @error('license_grace_period')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="license_auto_check" 
                                       value="1" 
                                       {{ old('license_auto_check', $settings['license_auto_check'] ?? config('license.auto_check', false)) ? 'checked' : '' }} 
                                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Auto-check license on every request</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 ml-6">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mr-1"></i>
                                Not recommended - may impact performance. Only enable for strict license enforcement.
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 mb-1">
                                        <i class="fas fa-sync-alt text-gray-600 mr-2"></i>
                                        Test Connection
                                    </p>
                                    <p class="text-xs text-gray-600">Verify that the License Manager API is accessible</p>
                                </div>
                                <button type="button" 
                                        onclick="testLicenseConnection()" 
                                        class="btn-secondary text-xs px-3 py-1.5">
                                    <i class="fas fa-plug mr-1"></i> Test API
                                </button>
                            </div>
                            <div id="license-test-result" class="mt-3 hidden"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Display Section -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-tv text-primary-600 mr-2"></i>
                        Customer Display Settings
                    </h3>
                    
                    <!-- Display Mode -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mode Customer Display</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all"
                                   :class="" 
                                   onclick="document.getElementById('display_mode_local').checked = true; document.querySelectorAll('[data-display-mode]').forEach(el => { el.classList.remove('border-primary-500','bg-primary-50'); el.classList.add('border-gray-200'); }); this.classList.remove('border-gray-200'); this.classList.add('border-primary-500','bg-primary-50');"                           
                                   data-display-mode
                                   class="{{ ($settings['customer_display_mode'] ?? 'local') === 'local' ? 'border-primary-500 bg-primary-50' : 'border-gray-200' }}">
                                <input type="radio" id="display_mode_local" name="customer_display_mode" value="local" class="mt-1 mr-3 text-primary-600" {{ ($settings['customer_display_mode'] ?? 'local') === 'local' ? 'checked' : '' }}>
                                <div>
                                    <div class="font-medium text-gray-900"><i class="fas fa-desktop mr-1"></i> Lokal (2 Layar, 1 Komputer)</div>
                                    <p class="text-xs text-gray-500 mt-1">Real-time tanpa delay. Customer display dibuka di monitor kedua dari komputer kasir yang sama.</p>
                                </div>
                            </label>
                            <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all"
                                   onclick="document.getElementById('display_mode_network').checked = true; document.querySelectorAll('[data-display-mode]').forEach(el => { el.classList.remove('border-primary-500','bg-primary-50'); el.classList.add('border-gray-200'); }); this.classList.remove('border-gray-200'); this.classList.add('border-primary-500','bg-primary-50');"
                                   data-display-mode
                                   class="{{ ($settings['customer_display_mode'] ?? 'local') === 'network' ? 'border-primary-500 bg-primary-50' : 'border-gray-200' }}">
                                <input type="radio" id="display_mode_network" name="customer_display_mode" value="network" class="mt-1 mr-3 text-primary-600" {{ ($settings['customer_display_mode'] ?? 'local') === 'network' ? 'checked' : '' }}>
                                <div>
                                    <div class="font-medium text-gray-900"><i class="fas fa-wifi mr-1"></i> Jaringan (Perangkat Lain)</div>
                                    <p class="text-xs text-gray-500 mt-1">Customer display di tablet/HP/PC lain dalam jaringan yang sama. Ada sedikit delay (~2 detik).</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium mb-1">Advertisement Poster</p>
                                <p class="text-blue-700">Upload an image to display as advertisement on the customer-facing display screen (left side).</p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer Display Poster</label>
                        <input type="file" name="customer_display_poster" accept="image/png,image/jpg,image/jpeg" class="input" onchange="previewPoster(event)">
                        <p class="text-xs text-gray-500 mt-1">Recommended: High resolution landscape image (1920x1080px). Max 10MB. Formats: PNG, JPG, JPEG</p>
                        @error('customer_display_poster')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        
                        <!-- Poster Preview -->
                        <div class="mt-4 flex items-center gap-4">
                            @if(isset($settings['customer_display_poster']) && $settings['customer_display_poster'])
                            <div id="current-poster-container" class="flex-1">
                                <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                    <img id="current-poster" src="{{ asset('storage/' . $settings['customer_display_poster']) }}" alt="Current Poster" class="w-full h-auto max-h-64 object-contain">
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-600 font-medium">Current Poster</p>
                                        <p class="text-xs text-gray-400">{{ basename($settings['customer_display_poster']) }}</p>
                                    </div>
                                    <button type="button" 
                                            onclick="removePoster()" 
                                            class="text-xs text-red-600 hover:text-red-800 hover:underline">
                                        <i class="fas fa-times-circle mr-1"></i>Remove Poster
                                    </button>
                                </div>
                            </div>
                            @endif
                            
                            <div id="new-poster-preview" class="hidden flex-1">
                                <div class="border-2 border-primary-200 rounded-lg overflow-hidden bg-primary-50">
                                    <img id="preview-poster-image" src="" alt="New Poster" class="w-full h-auto max-h-64 object-contain">
                                </div>
                                <div class="mt-2">
                                    <p class="text-xs text-primary-600 font-medium">New Poster Preview</p>
                                    <p id="preview-poster-filename" class="text-xs text-gray-400"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Limit Section -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-exclamation-triangle text-primary-600 mr-2"></i>
                        Order Limit
                    </h3>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                            <div class="text-sm text-blue-700">
                                <p>Jika diaktifkan, sistem akan menampilkan <strong>warning</strong> ketika total penjualan (rupiah) hari ini sudah mencapai limit. Order tetap bisa dilakukan, hanya peringatan saja.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="hidden" name="order_limit_enabled" value="0">
                                <input type="checkbox" name="order_limit_enabled" value="1" id="order_limit_enabled"
                                    class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2 cursor-pointer"
                                    {{ old('order_limit_enabled', $settings['order_limit_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label for="order_limit_enabled" class="text-sm font-medium text-gray-700 cursor-pointer">Aktifkan Limit Penjualan Harian</label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Centang untuk mengaktifkan fitur limit total penjualan per hari</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Limit Penjualan Per Hari (Rp)</label>
                            <input type="number" name="order_limit_amount" class="input" min="1"
                                value="{{ old('order_limit_amount', $settings['order_limit_amount'] ?? '') }}"
                                placeholder="Contoh: 5000000">
                            <p class="text-xs text-gray-500 mt-1">Total rupiah maksimal penjualan dalam 1 hari</p>
                            @error('order_limit_amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Berlaku Dari Jam</label>
                            <input type="time" name="order_limit_start" class="input"
                                value="{{ old('order_limit_start', $settings['order_limit_start'] ?? '00:00') }}">
                            <p class="text-xs text-gray-500 mt-1">Jam mulai berlaku limit</p>
                            @error('order_limit_start')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Berlaku Hingga Jam</label>
                            <input type="time" name="order_limit_end" class="input"
                                value="{{ old('order_limit_end', $settings['order_limit_end'] ?? '23:59') }}">
                            <p class="text-xs text-gray-500 mt-1">Jam berakhir limit</p>
                            @error('order_limit_end')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Backup Settings Section -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-database text-primary-600 mr-2"></i>
                        Backup Settings
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Backup</label>
                            <input type="text" name="backup_path" class="input" 
                                value="{{ old('backup_path', $settings['backup_path'] ?? '') }}" 
                                placeholder="Kosongkan untuk default (storage/app/backups)">
                            <p class="text-xs text-gray-500 mt-1">Path folder untuk menyimpan file backup. Contoh: D:\\backup\\pos-resto</p>
                            @error('backup_path')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="hidden" name="backup_schedule_1_enabled" value="0">
                                <input type="checkbox" name="backup_schedule_1_enabled" value="1" id="backup_schedule_1_enabled"
                                    class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2 cursor-pointer"
                                    {{ old('backup_schedule_1_enabled', $settings['backup_schedule_1_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label for="backup_schedule_1_enabled" class="text-sm font-medium text-gray-700 cursor-pointer">Jadwal Backup 1</label>
                            </div>
                            <input type="time" name="backup_schedule_1" class="input" 
                                value="{{ old('backup_schedule_1', $settings['backup_schedule_1'] ?? '12:00') }}">
                            <p class="text-xs text-gray-500 mt-1">Jam backup otomatis pertama</p>
                            @error('backup_schedule_1')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="hidden" name="backup_schedule_2_enabled" value="0">
                                <input type="checkbox" name="backup_schedule_2_enabled" value="1" id="backup_schedule_2_enabled"
                                    class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2 cursor-pointer"
                                    {{ old('backup_schedule_2_enabled', $settings['backup_schedule_2_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label for="backup_schedule_2_enabled" class="text-sm font-medium text-gray-700 cursor-pointer">Jadwal Backup 2</label>
                            </div>
                            <input type="time" name="backup_schedule_2" class="input" 
                                value="{{ old('backup_schedule_2', $settings['backup_schedule_2'] ?? '23:55') }}">
                            <p class="text-xs text-gray-500 mt-1">Jam backup otomatis kedua</p>
                            @error('backup_schedule_2')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Simpan Backup (hari)</label>
                            <input type="number" name="backup_keep_days" class="input" min="1" max="365"
                                value="{{ old('backup_keep_days', $settings['backup_keep_days'] ?? '30') }}">
                            <p class="text-xs text-gray-500 mt-1">Backup lebih lama dari ini akan dihapus otomatis</p>
                            @error('backup_keep_days')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Receipt Section -->
                <div class="pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-receipt text-primary-600 mr-2"></i>
                        Receipt Settings
                    </h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Footer Message</label>
                        <textarea name="receipt_footer" rows="3" class="input" placeholder="Thank you message...">{{ old('receipt_footer', $settings['receipt_footer'] ?? '') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">This message will appear at the bottom of receipts</p>
                        @error('receipt_footer')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Settings
                </button>
                <button type="reset" class="btn-secondary">
                    <i class="fas fa-undo mr-2"></i> Reset
                </button>
            </div>
        </form>
    </div>
    
    <!-- Preview Card -->
    <div class="card mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-eye text-primary-600 mr-2"></i>
            Current Settings Preview
        </h3>
        
        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Restaurant Name</p>
                    <p class="font-semibold text-gray-900">{{ $settings['restaurant_name'] ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Phone</p>
                    <p class="font-semibold text-gray-900">{{ $settings['restaurant_phone'] ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Tax</p>
                    <p class="font-semibold text-gray-900">{{ $settings['tax_percentage'] ?? '0' }}% ({{ ($settings['tax_type'] ?? 'exclude') === 'include' ? 'Include' : 'Exclude' }})</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Service Charge</p>
                    <p class="font-semibold text-gray-900">{{ $settings['service_charge'] ?? '0' }}%</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewLogo(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('preview-filename').textContent = file.name;
            document.getElementById('new-logo-preview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}

async function testLicenseConnection() {
    const resultDiv = document.getElementById('license-test-result');
    const serverUrl = document.querySelector('input[name="license_server_url"]').value;
    const licenseKey = document.querySelector('input[name="license_key"]').value;
    
    if (!serverUrl || !licenseKey) {
        resultDiv.innerHTML = `
            <div class="bg-yellow-50 border border-yellow-200 rounded p-3 text-sm text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Please fill in License Server URL and License Key first.
            </div>
        `;
        resultDiv.classList.remove('hidden');
        return;
    }
    
    // Show loading
    resultDiv.innerHTML = `
        <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm text-blue-800">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Testing connection to ${serverUrl}...
        </div>
    `;
    resultDiv.classList.remove('hidden');
    
    try {
        const response = await fetch('/admin/test-license-connection', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                server_url: serverUrl,
                license_key: licenseKey
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded p-3 text-sm text-green-800">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong>✅ ${data.message}</strong><br>
                    <div class="mt-2 pl-5 space-y-1">
                        <div class="text-xs">📝 License Type: <strong>${data.license_type || 'N/A'}</strong></div>
                        <div class="text-xs">📊 Status: <strong>${data.status || 'N/A'}</strong></div>
                        ${data.customer_name ? `<div class="text-xs">👤 Customer: <strong>${data.customer_name}</strong></div>` : ''}
                    </div>
                </div>
            `;
        } else {
            let debugInfo = '';
            if (data.debug) {
                debugInfo = `
                    <details class="mt-2 text-xs">
                        <summary class="cursor-pointer hover:text-red-900">Show debug info</summary>
                        <pre class="mt-1 p-2 bg-red-100 rounded overflow-x-auto">${JSON.stringify(data.debug, null, 2)}</pre>
                    </details>
                `;
            }
            
            resultDiv.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded p-3 text-sm text-red-800">
                    <i class="fas fa-times-circle mr-2"></i>
                    <strong>❌ Connection Failed!</strong><br>
                    <span class="text-xs mt-1 block">${data.message || 'Unknown error occurred.'}</span>
                    ${debugInfo}
                    ${!data.debug || !data.debug.endpoint ? `
                        <div class="mt-3 p-2 bg-red-100 border border-red-300 rounded text-xs">
                            <strong>Troubleshooting Steps:</strong>
                            <ol class="list-decimal list-inside mt-1 space-y-1">
                                <li>Make sure License Manager is running</li>
                                <li>Verify the server URL is correct (e.g., http://localhost:8000)</li>
                                <li>Check that the license key exists in License Manager</li>
                                <li>Ensure license status is 'active' in License Manager</li>
                            </ol>
                        </div>
                    ` : ''}
                </div>
            `;
        }
    } catch (error) {
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded p-3 text-sm text-red-800">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <strong>Error:</strong> ${error.message}
                <div class="mt-2 p-2 bg-red-100 rounded text-xs">
                    <strong>Common causes:</strong>
                    <ul class="list-disc list-inside mt-1">
                        <li>Network connection issue</li>
                        <li>CORS policy blocking the request</li>
                        <li>Server not responding</li>
                    </ul>
                </div>
            </div>
        `;
    }
}

function toggleMidtransFields(enabled) {
    const credentialsDiv = document.getElementById('midtrans-credentials');
    const disabledNotice = document.getElementById('midtrans-disabled-notice');
    
    if (enabled) {
        credentialsDiv.classList.remove('hidden');
        disabledNotice.classList.add('hidden');
    } else {
        credentialsDiv.classList.add('hidden');
        disabledNotice.classList.remove('hidden');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const midtransCheckbox = document.querySelector('input[name="midtrans_enabled"]');
    if (midtransCheckbox) {
        toggleMidtransFields(midtransCheckbox.checked);
    }
});

async function removeLogo() {
    if (!confirm('Are you sure you want to remove the logo? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/settings/remove-logo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove the current logo preview from DOM
            const logoContainer = document.getElementById('current-logo-container');
            if (logoContainer) {
                logoContainer.remove();
            }
            
            // Show success message
            alert('Logo removed successfully!');
        } else {
            alert('Failed to remove logo: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Error removing logo: ' + error.message);
    }
}

function previewPoster(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-poster-image').src = e.target.result;
            document.getElementById('preview-poster-filename').textContent = file.name;
            document.getElementById('new-poster-preview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}

async function removePoster() {
    if (!confirm('Are you sure you want to remove the customer display poster? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/settings/remove-poster', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove the current poster preview from DOM
            const posterContainer = document.getElementById('current-poster-container');
            if (posterContainer) {
                posterContainer.remove();
            }
            
            // Show success message
            alert('Poster removed successfully!');
        } else {
            alert('Failed to remove poster: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Error removing poster: ' + error.message);
    }
}
</script>
@endsection
