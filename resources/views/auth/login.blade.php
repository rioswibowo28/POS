@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-500 to-primary-700 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            @php
                $restaurantName = \App\Models\Setting::get('restaurant_name', 'POS Resto');
                $restaurantLogo = \App\Models\Setting::get('restaurant_logo');
            @endphp
            
            <!-- Logo Full Size -->
            <div class="mx-auto flex items-center justify-center">
                @if($restaurantLogo)
                    <img src="{{ asset('storage/' . $restaurantLogo) }}" alt="{{ $restaurantName }}" class="w-30 h-30 object-contain drop-shadow-2xl">
                @else
                    <i class="fas fa-utensils text-8xl text-white drop-shadow-2xl"></i>
                @endif
            </div>
            
            <h2 class="text-4xl font-extrabold text-white">
                {{ $restaurantName }}
            </h2>
            <p class="mt-2 text-sm text-primary-100">
                Point of Sale Restaurant Management System
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Sign In</h3>
            
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-6">
                @csrf

                <!-- Username or Email -->
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 mb-2">
                        Username atau Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input id="login" 
                               name="login" 
                               type="text" 
                               autocomplete="username" 
                               required 
                               value="{{ old('login') }}"
                               class="input pl-10 @error('login') border-red-500 @enderror" 
                               placeholder="Username atau email">
                    </div>
                    @error('login')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" 
                               name="password" 
                               type="password" 
                               autocomplete="current-password" 
                               required 
                               class="input pl-10 @error('password') border-red-500 @enderror" 
                               placeholder="Enter your password">
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" 
                               name="remember" 
                               type="checkbox" 
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-primary-600 hover:text-primary-500">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In
                    </button>
                </div>
            </form>

            <!-- Demo Accounts -->
            <div class="mt-6 pt-6 border-t border-gray-200">
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-8 text-center text-sm text-primary-100">
            &copy; 2026 UwaisTech. All rights reserved.
        </p>
    </div>
</div>
@endsection
