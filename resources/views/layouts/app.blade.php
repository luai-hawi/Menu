<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Hawi Tech') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Force Dark Theme -->
    <style>
        body, html { background-color: #0f172a !important; color: #e2e8f0 !important; }
        nav { background-color: #1e293b !important; border-color: #475569 !important; }
        input, textarea, select, button { background-color: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
        input:focus, textarea:focus, select:focus { background-color: #4b5563 !important; border-color: #3b82f6 !important; }
        .bg-white { background-color: #1e293b !important; }
        .text-gray-900, .text-gray-800 { color: #f9fafb !important; }
        .text-gray-700 { color: #d1d5db !important; }
        .border-gray-200, .border-gray-300 { border-color: #475569 !important; }
        [x-transition] { background-color: #1e293b !important; border-color: #475569 !important; }
        .bg-gray-800 { background-color: #374151 !important; }
        .hover\:bg-gray-700:hover { background-color: #4b5563 !important; }
        a { color: #d1d5db !important; }
        a:hover { color: #ffffff !important; }
        .menu-image { width: 80px !important; height: 80px !important; object-fit: cover !important; }
    </style>
</head>
    <body class="font-sans antialiased bg-darker text-gray-100">
        <div class="min-h-screen bg-darker">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="header-gradient shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="bg-darker min-h-screen">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>