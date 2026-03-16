<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Models\Setting::get('restaurant_name', 'POS Resto'))</title>
    
    @php
        $logo = \App\Models\Setting::get('restaurant_logo');
    @endphp
    
    @if($logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $logo) }}">
    @endif
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased">
    @yield('content')
</body>
</html>
