<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
            @if (isset($header))
                {{ strip_tags($header->toHtml()) }} - FDTS
            @else
                {{ config('app.name') }} - FDTS
            @endif
        </title>
        <link rel="icon" href="{{ asset('fns_Logo.ico') }}" type="image/x-icon">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Saysettha+OT&display=swap" rel="stylesheet">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        {{-- ===== เพิ่ม JS ของ Flatpickr ที่นี่ (ก่อนปิด body) ===== --}}
        {{-- โหลด Library หลักของ Flatpickr --}}
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        {{-- โหลดไฟล์แปลภาษาลาว (ที่เราสร้างเองหรือใช้ของไทย) --}}
        <script src="{{ asset('js/flatpickr-locale-lo.js') }}"></script>

        <script src="https://npmcdn.com/flatpickr/dist/plugins/momentPlugin.js"></script>
        {{-- เราจะเพิ่ม JS ของเราเองด้วย --}}
        <script>
        // 1. สร้างฟังก์ชันกลางชื่อ initDatepicker
            function initDatepicker(element) {
                flatpickr(element, {
                    "locale": "lo",
                    allowInput: true,
                    altInput: true,
                    altFormat: "d/m/Y",
                    dateFormat: "Y-m-d",
                });
            }
        </script>
        <!--
        <script>
            flatpickr(".datepicker", {
                // ใช้ Plugin
                "plugins": [new momentPlugin({
                    // บอก Plugin ว่าให้ใช้รูปแบบนี้ในการ Parse และ Format
                    moment: {
                        parse: "DD/MM/YYYY",
                        format: "DD/MM/YYYY",
                    }
                })],
                dateFormat: "d/m/Y",    // กำหนดรูปแบบการแสดงผล
                allowInput: true,       // <-- เพิ่มบรรทัดนี้
                altInput: true,         // (Optional) แสดงรูปแบบที่อ่านง่าย แต่ส่งค่ามาตรฐาน
                altFormat: "j F Y",     // (Optional) รูปแบบที่อ่านง่าย (เช่น 1 กันยายน 2025)
            });
        </script>--><!--
        <script>
            // โค้ดนี้จะทำงานกับ "ทุก" input ที่มี class="datepicker" ในระบบ
            flatpickr(".datepicker", {
                "locale": "lo", 
                allowInput: true,
                dateFormat: "Y-m-d", // ส่งค่า Y-m-d ไปที่ Backend
                altInput: true,
                altFormat: "d/m/Y", // แสดง dd/mm/yyyy ให้ผู้ใช้เห็น
            });
        </script>-->
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
