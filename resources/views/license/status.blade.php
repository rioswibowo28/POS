<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('License Status') }}
            </h2>
            <a href="{{ route('license.refresh') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Refresh License
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($license['valid'])
                        <div class="mb-6">
                            <div class="flex items-center">
                                <svg class="w-12 h-12 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-2xl font-bold text-green-600">License Active</h3>
                                    <p class="text-gray-600">Your license is valid and active</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase">License Key</h4>
                                <p class="mt-2 text-lg font-mono">{{ $license['license_key'] }}</p>
                            </div>

                            <div class="border rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase">Status</h4>
                                <p class="mt-2 text-lg">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-semibold">
                                        {{ strtoupper($license['status']) }}
                                    </span>
                                </p>
                            </div>

                            <div class="border rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase">Expiry Date</h4>
                                <p class="mt-2 text-lg">{{ $license['expiry_date'] ?? 'Lifetime' }}</p>
                            </div>

                            <div class="border rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase">Days Remaining</h4>
                                <p class="mt-2 text-lg">
                                    @if($license['days_remaining'] === null)
                                        <span class="text-blue-600 font-semibold">Lifetime</span>
                                    @elseif($license['days_remaining'] < 0)
                                        <span class="text-red-600 font-semibold">Expired</span>
                                    @elseif($license['days_remaining'] <= 30)
                                        <span class="text-orange-600 font-semibold">{{ $license['days_remaining'] }} days</span>
                                    @else
                                        <span class="text-green-600 font-semibold">{{ $license['days_remaining'] }} days</span>
                                    @endif
                                </p>
                            </div>

                            <div class="border rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase">Max Users</h4>
                                <p class="mt-2 text-lg">{{ $license['max_users'] ?? 'Unlimited' }}</p>
                            </div>

                            <div class="border rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase">Max Tables</h4>
                                <p class="mt-2 text-lg">{{ $license['max_tables'] ?? 'Unlimited' }}</p>
                            </div>
                        </div>

                        @if($license['days_remaining'] !== null && $license['days_remaining'] > 0 && $license['days_remaining'] <= 30)
                            <div class="mt-6 bg-orange-50 border-l-4 border-orange-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-orange-700">
                                            <strong>Warning:</strong> Your license will expire in {{ $license['days_remaining'] }} days. 
                                            Please contact your administrator or support to renew your license.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-6 flex space-x-4">
                            <a href="{{ route('license.offline-activation') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Get Offline Activation Code
                            </a>
                        </div>

                    @else
                        <div class="mb-6">
                            <div class="flex items-center">
                                <svg class="w-12 h-12 text-red-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-2xl font-bold text-red-600">License Invalid</h3>
                                    <p class="text-gray-600">{{ $license['message'] ?? 'Your license is invalid or expired' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                            <p class="text-red-700">
                                Please contact your administrator or support to update your license key.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
