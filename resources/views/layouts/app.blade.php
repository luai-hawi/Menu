<!DOCTYPE html>
@php
    $appLocale = app()->getLocale();
    $appDir    = $appLocale === 'ar' ? 'rtl' : 'ltr';
@endphp
<html lang="{{ str_replace('_', '-', $appLocale) }}" dir="{{ $appDir }}" class="dark">
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
    @if($appLocale === 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @endif
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">


    <!-- Scripts -->
    <script>
        // Translation strings accessed by window.kiloSubmit / kiloAction / toasts.
        window.KiloI18n = {
            saving:            @js(__('messages.products.saving')),
            saved:             @js(__('messages.products.saved')),
            saveFailed:        @js(__('messages.products.save_failed')),
            somethingWrong:    @js(__('messages.products.something_went_wrong')),
            validationFailed:  @js(__('messages.errors.validation_failed')),
            networkError:     @js(__('messages.errors.network_error')),
            confirmDelete:    @js(__('messages.form.confirm_delete')),
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Force Dark Theme -->
    <style>
        @if($appLocale === 'ar')
            body, html { font-family: 'Cairo', 'Inter', sans-serif !important; }
        @endif
        /* Logical-property helpers for RTL friendliness. */
        html[dir="rtl"] .rtl\:text-right { text-align: right; }
        html[dir="rtl"] .rtl\:text-left  { text-align: left; }
        body, html { background-color: #0f172a !important; color: #e2e8f0 !important; }

        /* ---- Toast notification host ---- */
        .toast-host {
            position: fixed;
            top: 1rem;
            inset-inline-end: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-width: 22rem;
            pointer-events: none;
        }
        .toast {
            pointer-events: auto;
            background: #1e293b;
            color: #f1f5f9;
            border: 1px solid #334155;
            border-inline-start: 4px solid #64748b;
            padding: 0.85rem 1rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            opacity: 0;
            transform: translateX(20px);
            transition: opacity .25s, transform .25s;
        }
        html[dir="rtl"] .toast { transform: translateX(-20px); }
        .toast-visible { opacity: 1; transform: translateX(0) !important; }
        .toast-success { border-inline-start-color: #22c55e; background: #0f2a1a; color: #bbf7d0; }
        .toast-error   { border-inline-start-color: #ef4444; background: #2a0f14; color: #fecaca; }
        .toast-info    { border-inline-start-color: #3b82f6; background: #0f1d2a; color: #bfdbfe; }
        .toast-icon {
            display: inline-flex;
            width: 1.5rem; height: 1.5rem;
            align-items: center; justify-content: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            font-weight: 700;
            flex-shrink: 0;
        }
        .toast-text { flex: 1; font-size: .9rem; line-height: 1.4; }
        .toast-close {
            background: transparent; border: 0; color: inherit;
            font-size: 1.25rem; line-height: 1; cursor: pointer; opacity: .6;
        }
        .toast-close:hover { opacity: 1; }

        /* ---- Button spinner for AJAX-pending state ---- */
        .btn-spinner {
            display: inline-block;
            width: 14px; height: 14px;
            margin-inline-end: 0.5rem;
            border: 2px solid rgba(255,255,255,0.35);
            border-top-color: rgba(255,255,255,0.95);
            border-radius: 50%;
            animation: kilo-spin 0.7s linear infinite;
            vertical-align: -2px;
        }
        @keyframes kilo-spin { to { transform: rotate(360deg); } }
        button[data-loading="1"] { opacity: 0.8; cursor: wait; }

        /* ---- Sortable visual hints ---- */
        .sortable-ghost { opacity: 0.4; }
        .sortable-drag  { opacity: 0.9 !important; }
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