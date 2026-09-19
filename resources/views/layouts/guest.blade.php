<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'FDTS') }}</title>
        <link rel="icon" href="{{ asset('fns_Logo.ico') }}" type="image/x-icon">
        
        <!-- Fonts (ປ່ຽນມາໃຊ້ Noto Sans Lao ເພື່ອໃຫ້ສະແດງພາສາລາວງາມຂຶ້ນ) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- PWA Manifest & Meta Tags -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#2563eb">
        
        <!-- ສຳລັບ iOS Safari -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="FDTS">
        <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- ບັງຄັບໃຫ້ທັງໜ້າໃຊ້ຟອນພາສາລາວ --}}
        <style>
            body { font-family: 'Noto Sans Lao', sans-serif; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0">
            
            {{-- ສ່ວນໂລໂກ້: ປັບຂະໜາດໃຫ້ເປັນ Responsive (ມືຖື h-32, ຄອມ h-48) --}}
            <div>
                <a href="/">
                    <x-application-logo class="w-auto h-32 sm:h-40 md:h-48 fill-current text-gray-500 drop-shadow-sm transition-all duration-300" />
                </a>
            </div>

            {{-- ສ່ວນຂອງຟອມ Login/Register --}}
            <div class="w-full sm:max-w-md mt-8 px-6 py-8 bg-white shadow-lg overflow-hidden sm:rounded-2xl border border-gray-100">
                {{ $slot }}
            </div>
            
        </div>

        <!-- PWA Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then(registration => {
                            console.log('ServiceWorker registered successfully with scope: ', registration.scope);
                        })
                        .catch(error => {
                            console.log('ServiceWorker registration failed: ', error);
                        });
                });
            }
        </script>
    </body>
</html>