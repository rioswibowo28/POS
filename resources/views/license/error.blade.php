@extends('layouts.app')
@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('License Error') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <svg class="w-16 h-16 text-red-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h3 class="text-3xl font-bold text-red-600">License Invalid or Expired</h3>
                            <p class="text-gray-600 mt-2">Your POS Resto license is not valid</p>
                        </div>
                    </div>

                    @if(session('error'))
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Unable to access POS Resto</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>Your license may be:</p>
                                    <ul class="list-disc list-inside mt-2 space-y-1">
                                        <li>Expired or not activated</li>
                                        <li>Invalid or revoked</li>
                                        <li>Not properly configured</li>
                                        <li>Unable to connect to license server</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold text-gray-700 mb-2">What to do:</h4>
                        <ol class="list-decimal list-inside space-y-2 text-gray-600">
                            <li>Check your internet connection</li>
                            <li>Verify your license key in <code class="bg-gray-200 px-2 py-1 rounded">.env</code> file</li>
                            <li>Contact your administrator or license provider</li>
                            <li>Check license server URL configuration</li>
                        </ol>
                    </div>

                    <div class="flex space-x-4">
                        <a href="{{ route('license.status') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Check License Status
                        </a>
                        <a href="{{ route('license.refresh') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Refresh License
                        </a>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <h4 class="font-semibold text-gray-700 mb-2">Support Information:</h4>
                        <p class="text-gray-600">License Key (in .env): <code class="bg-gray-200 px-2 py-1 rounded">{{ config('license.license_key') ?: 'Not configured' }}</code></p>
                        <p class="text-gray-600">License Server: <code class="bg-gray-200 px-2 py-1 rounded">{{ config('license.server_url') }}</code></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
