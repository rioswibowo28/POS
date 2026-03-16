<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" x-cloak>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ \App\Models\Setting::get('restaurant_name', 'POS Resto') }}</title>
    
    @php
        $logo = \App\Models\Setting::get('restaurant_logo');
    @endphp
    
    @if($logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $logo) }}">
    @endif
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 bg-gradient-to-b from-gray-900 to-gray-800 shadow-2xl transform transition-all duration-300 lg:translate-x-0 lg:static lg:inset-0"
               :class="sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0' + (sidebarCollapsed ? ' lg:w-20' : ' lg:w-64')">
            
            <!-- Logo & Toggle -->
            <div class="bg-gray-900 border-b border-gray-700">
                <div class="flex items-center justify-between h-16" :class="sidebarCollapsed ? 'px-2' : 'px-4'">
                    @php
                        $restaurantName = \App\Models\Setting::get('restaurant_name', 'POS Resto');
                        $restaurantLogo = \App\Models\Setting::get('restaurant_logo');
                    @endphp
                    
                    <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center w-full' : 'gap-3'">
                        @if($restaurantLogo)
                            <img src="{{ asset('storage/' . $restaurantLogo) }}" 
                                 alt="{{ $restaurantName }}" 
                                 class="object-contain transition-all duration-300"
                                 :class="sidebarCollapsed ? 'h-10 w-10' : 'h-12 w-12'">
                            <h1 class="font-bold text-primary-400 transition-all duration-300 text-lg"
                                x-show="!sidebarCollapsed"
                                x-transition>
                                {{ $restaurantName }}
                            </h1>
                        @else
                            <i class="fas fa-utensils text-primary-400 transition-all duration-300" :class="sidebarCollapsed ? 'text-2xl' : 'text-2xl'"></i>
                            <h1 class="font-bold text-primary-400 text-lg"
                                x-show="!sidebarCollapsed"
                                x-transition>
                                {{ $restaurantName }}
                            </h1>
                        @endif
                    </div>
                    
                    <!-- Desktop Toggle Button (when expanded) -->
                    <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)" 
                            class="text-gray-400 hover:text-white transition-colors duration-200 ml-4 hidden lg:block"
                            x-show="!sidebarCollapsed"
                            title="Collapse Sidebar">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>

                <!-- Collapsed Toggle Button (when collapsed) -->
                <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)"
                        class="w-full justify-center py-3 text-gray-400 hover:text-white hover:bg-gray-700 transition-all duration-200 hidden lg:flex"
                        x-show="sidebarCollapsed">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="mt-4 px-2 overflow-y-auto" style="max-height: calc(100vh - 180px);">
                {{-- === Menu untuk SEMUA role (kasir & admin) === --}}
                <a href="{{ route('dashboard') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('dashboard') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-home" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3 transition-all duration-300" 
                          x-show="!sidebarCollapsed"
                          x-transition>Dashboard</span>
                </a>
                
                <a href="{{ route('pos.index') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('pos.*') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-cash-register" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>POS / Kasir</span>
                </a>
                
                <a href="{{ route('orders.index') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('orders.*') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-receipt" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>Orders</span>
                </a>
                
                @if(\App\Models\Setting::get('use_shifts', true))
                <a href="{{ route('shifts.index') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('shifts.*') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-clock" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>Shifts</span>
                </a>
                @endif

                @if(auth()->user()->canAccessReports())
                <div x-data="{ reportOpen: {{ request()->routeIs('reports.*') || request()->routeIs('dynamic-reports.*') ? 'true' : 'false' }} }">
                    <div @click="reportOpen = !reportOpen"
                       class="sidebar-item relative flex items-center mb-1 cursor-pointer select-none text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('reports.*') || request()->routeIs('dynamic-reports.*') ? 'bg-gray-700 !text-white' : '' }}"
                       :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                        <i class="fas fa-chart-bar" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                        <span class="ml-3 flex-1 text-left" x-show="!sidebarCollapsed" x-transition>Reports</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" x-show="!sidebarCollapsed" :class="reportOpen ? 'rotate-180' : ''"></i>
                    </div>
                    <div x-show="reportOpen && !sidebarCollapsed" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="ml-4 pl-4 border-l border-gray-700 space-y-1 mt-1">
                        <a href="{{ route('reports.index') }}" 
                           class="flex items-center py-2 px-3 text-sm rounded-lg transition-all duration-200 {{ request()->routeIs('reports.index') ? 'bg-primary-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                            <i class="fas fa-chart-line w-4 text-xs mr-2"></i> Overview
                        </a>
                        <a href="{{ route('reports.tax-sales') }}" 
                           class="flex items-center py-2 px-3 text-sm rounded-lg transition-all duration-200 {{ request()->routeIs('reports.tax-sales*') ? 'bg-primary-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                            <i class="fas fa-file-invoice w-4 text-xs mr-2"></i> Penjualan Detail & Rekap
                        </a>
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('reports.internal-revenue') }}"
                           class="flex items-center py-2 px-3 text-sm rounded-lg transition-all duration-200 {{ request()->routeIs('reports.internal-revenue*') ? 'bg-primary-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                            <i class="fas fa-money-bill-wave w-4 text-xs mr-2"></i> Penjualan ALL
                        </a>
                        @endif
                        
                        <a href="{{ route('dynamic-reports.index') }}"
                           class="flex items-center py-2 px-3 text-sm rounded-lg transition-all duration-200 {{ request()->routeIs('dynamic-reports.*') ? 'bg-primary-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                            <i class="fas fa-list-alt w-4 text-xs mr-2"></i> User Defined Reports
                        </a>
                    </div>
                </div>
                @endif
                
                {{-- === Menu ADMIN only === --}}
                @if(auth()->user()->isAdmin())
                <div class="my-3" x-show="!sidebarCollapsed" x-transition>
                    <div class="border-t border-gray-700"></div>
                </div>
                
                <a href="{{ route('products.index') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('products.*') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-box" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>Products</span>
                </a>
                
                <a href="{{ route('categories.index') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('categories.*') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-tags" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>Categories</span>
                </a>
                
                <a href="{{ route('tables.index') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('tables.*') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-table" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>Tables</span>
                </a>

                <a href="{{ route('master-shifts.index') }}"
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('master-shifts.*') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-clock" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>Master Shifts</span>
                </a>
                </a>
                
                <div class="my-3" x-show="!sidebarCollapsed" x-transition>
                    <div class="border-t border-gray-700"></div>
                </div>
                
                <a href="{{ route('users.index') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('users.*') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-users" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>Users</span>
                </a>
                
                <a href="{{ route('license.info') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('license.info') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-key" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>License</span>
                </a>
                
                <a href="{{ route('backups.index') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('backups.*') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-database" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>Backup</span>
                </a>

                <a href="{{ route('settings.index') }}" 
                   class="sidebar-item relative flex items-center mb-1 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition-all duration-200 group {{ request()->routeIs('settings.*') ? 'bg-primary-600 text-white' : '' }}"
                   :class="sidebarCollapsed ? 'px-3 py-3 justify-center' : 'px-4 py-3'">
                    <i class="fas fa-cog" :class="sidebarCollapsed ? 'text-xl' : 'text-base w-5'"></i>
                    <span class="ml-3" x-show="!sidebarCollapsed" x-transition>Settings</span>
                </a>
                @endif
            </nav>
            
            <!-- User Info -->
            <div class="absolute bottom-0 w-full border-t border-gray-700 transition-all duration-300"
                 :class="sidebarCollapsed ? 'p-2' : 'p-4'">
                <div class="relative flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="rounded-full bg-primary-600 flex items-center justify-center text-white font-bold transition-all duration-300"
                             :class="sidebarCollapsed ? 'w-10 h-10' : 'w-10 h-10'">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                    <!-- User details in expanded mode -->
                    <div class="ml-3 flex-1" x-show="!sidebarCollapsed" x-transition>
                        <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
                    </div>
                    <!-- Logout button -->
                    <form method="POST" action="{{ route('logout') }}" x-show="!sidebarCollapsed" x-transition>
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-white transition-colors duration-200"
                                title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="flex flex-col flex-1 overflow-hidden">
            
            <!-- Header -->
            <header class="flex items-center justify-between px-6 py-4 bg-white border-b">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none lg:hidden">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <h2 class="text-2xl font-semibold text-gray-800 ml-4 lg:ml-0">
                        @yield('header', 'Dashboard')
                    </h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600">
                        <i class="far fa-clock mr-1"></i>
                        <span id="current-time"></span>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ session('warning') }}
                        </div>
                        <a href="{{ route('license.info') }}" class="text-yellow-800 underline hover:text-yellow-900 ml-4">
                            Lihat Detail
                        </a>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Overlay for mobile -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>
    
    <script>
        // Update time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateString = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            const hijriString = now.toLocaleDateString('id-ID-u-ca-islamic', { day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('current-time').textContent = hijriString + '  |  ' + dateString + ' ' + timeString;
        }
        updateTime();
        setInterval(updateTime, 1000);
    </script>
    
    @stack('scripts')
</body>
</html>
