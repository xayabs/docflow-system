<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ລະບົບຕິດຕາມເອກະສານການເງິນ</title>
    <link rel="icon" href="{{ asset('fns_Logo.ico') }}" type="image/x-icon">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- PWA Manifest & Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="FDTS">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

    <!-- ສຳລັບ Push Notification -->
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <meta name="push-subscribe-url" content="{{ route('push.subscribe') }}">
    
    <!-- CSRF Token (ມີຢູ່ແລ້ວໃນ app.blade.php ແຕ່ອາດຕ້ອງເພີ່ມໃນ welcome.blade.php) -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Styles (ใช้ Tailwind CSS จาก Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Noto Sans Lao', sans-serif;
        }
    </style>
</head>
<body class="antialiased">
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white" x-data="{ showAboutModal: false }">
        
        {{-- ເມນູມຸມເທິງຊ້າຍ-ຂວາ --}}
        <div class="fixed top-0 left-0 right-0 z-10 flex justify-between items-center p-4 sm:p-6 bg-white/80 backdrop-blur-sm sm:bg-transparent sm:backdrop-blur-none border-b border-gray-200 sm:border-none shadow-sm sm:shadow-none">
            <div>
                <a href="{{ asset('user_manual.pdf') }}" target="_blank" class="font-semibold text-sm sm:text-base text-blue-600 hover:text-blue-800 transition">
                    &darr; ຄູ່ມືການນໍາໃຊ້
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="#" @click.prevent="showAboutModal = true" class="font-semibold text-sm sm:text-base text-gray-600 hover:text-gray-900 transition">
                    ກ່ຽວກັບໂປຣແກຣມ
                </a>
                
                @auth
                    <a href="{{ url('/dashboard') }}" class="font-semibold text-sm sm:text-base text-blue-600 hover:text-blue-800 transition">Dashboard &rarr;</a>
                @endauth
            </div>
        </div>
        
        {{-- ສ່ວນ Hero Section --}}
        <div class="max-w-7xl mx-auto p-6 lg:p-8 w-full mt-16 sm:mt-0">
            <div class="flex justify-center">
                {{-- ໂລໂກ້ປັບຂະໜາດອັດຕະໂນມັດ --}}
                <img src="{{ asset('images/nuol_logo.png') }}" alt="NUOL Logo" class="w-full max-w-[200px] sm:max-w-xs md:max-w-sm lg:max-w-md object-contain mx-auto">
            </div>
            
            <div class="mt-8 text-center px-4">
                {{-- ຫົວຂໍ້ຫຼັກ --}}
                <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
                    ລະບົບຕິດຕາມເອກະສານການເງິນ
                </h1>
                
                {{-- ຂໍ້ຄວາມຍ່ອຍ 1 --}}
                <p class="mt-4 text-sm sm:text-base md:text-lg text-gray-600 dark:text-gray-400">
                    ຄະນະວິທະຍາສາດທຳມະຊາດ | ມະຫາວິທະຍາໄລແຫ່ງຊາດ
                </p>
                
                {{-- ຂໍ້ຄວາມຍ່ອຍ 2 --}}
                <p class="mt-2 text-xs sm:text-sm md:text-base text-gray-500">
                    ຕິດຕາມສະຖານະ, ເພີ່ມຄວາມໂປ່ງໃສ, ຫຼຸດຜ່ອນການນຳໃຊ້ເຈ້ຍ.
                </p>
            </div>
            
            {{-- ປຸ່ມ Call to Action --}}
            @guest
                <div class="mt-12 flex justify-center">
                    <a href="{{ route('login') }}" class="w-full sm:w-auto text-center px-8 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-lg text-white hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition shadow-md">
                        ເຂົ້າສູ່ລະບົບ
                    </a>
                </div>
            @endguest
            
            <div class="mt-16 flex justify-center">
                <p class="text-center text-xs sm:text-sm text-gray-500">
                    © {{ date('Y') }} ຄະນະວິທະຍາສາດທຳມະຊາດ | ມະຫາວິທະຍາໄລແຫ່ງຊາດ
                </p>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- ===== ໂຄດ Modal "ກ່ຽວກັບໂປຣແກຣມ" ===== --}}
        {{-- ========================================================== --}}
        <div x-show="showAboutModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0"
            style="display: none;">

            <!-- Background Overlay -->
            <div @click="showAboutModal = false" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Content (ແກ້ໄຂໃຫ້ມີ max-h ແລະ overflow-y-auto) -->
            <div @click.stop class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10">
                
                {{-- Modal Header --}}
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-2xl">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800">ກ່ຽວກັບລະບົບ</h2>
                    <button @click="showAboutModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            
                {{-- Modal Body --}}
                <div class="p-6 text-sm sm:text-base text-gray-700 space-y-6">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <p class="leading-relaxed">
                            ລະບົບນີ້ຖືກພັດທະນາຂຶ້ນເພື່ອເປັນສ່ວນໜຶ່ງຂອງລະບົບຄຸ້ມຄອງ ແລະ ບໍລິຫານການເງິນ, ຄະນະວິທະຍາສາດທຳມະຊາດ, ມະຫາວິທະຍາໄລແຫ່ງຊາດ.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center mb-3">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            1. ຂໍ້ມູນພື້ນຖານຂອງໂປຣແກຣມ:
                        </h3>
                        <ul class="list-none space-y-3 pl-2 sm:pl-7">
                            <li><span class="font-semibold text-gray-900">ຊື່ໂປຣແກຣມ:</span> ລະບົບຕິດຕາມເອກະສານການເງິນ</li>
                            <li><span class="font-semibold text-gray-900">ຮຸ່ນ (Version):</span> 1.0.0</li>
                            <li>
                                <span class="font-semibold text-gray-900 block mb-1">ຄຳອະທິບາຍໂດຍຫຍໍ້:</span>
                                <div class="pl-4 border-l-4 border-blue-200 text-gray-600 bg-gray-50 p-3 rounded-r-lg">
                                    <p class="leading-relaxed">ໂປຣແກຣມນີ້ຖືກພັດທະນາຂຶ້ນ ເພື່ອເພີ່ມປະສິດທິພາບໃນການບໍລິຫານຈັດການເອກະສານທາງດ້ານການເງິນຂອງຄະນະວິທະຍາສາດທໍາມະຊາດ. ລະບົບຈະຊ່ວຍໃຫ້ສາມາດຕິດຕາມຂັ້ນຕອນ ແລະ ການເຄື່ອນໄຫວຂອງເອກະສານໄດ້ຢ່າງເປັນລະບົບ, ຫຼຸດຜ່ອນການສູນຫາຍ, ສ້າງຄວາມໂປ່ງໃສ ແລະ ຊ່ວຍໃຫ້ການຄົ້ນຫາຂໍ້ມູນໃນອະດີດສາມາດເຮັດໄດ້ຢ່າງງ່າຍດາຍ.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                
                    <div class="pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center mb-3">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                            2. ຂໍ້ມູນຜູ້ພັດທະນາ (Developer):
                        </h3>
                        <ul class="list-none space-y-2 pl-2 sm:pl-7">
                            <li><span class="font-semibold text-gray-900">ຊື່ຜູ້ພັດທະນາ:</span> ອາຈານ ບົວສົດ ໄຊຍະຈັກ</li>
                            <li><span class="font-semibold text-gray-900">ຂໍ້ມູນຕິດຕໍ່ (Contact):</span>
                                <ul class="mt-1.5 space-y-1 text-gray-600 pl-4 border-l-2 border-gray-200">
                                    <li class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> +(856) 20 22245134</li>
                                    <li class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> bouasoth@nuol.edu.la</li>
                                </ul>
                            </li>
                            <li class="pt-2"><span class="font-semibold text-gray-900">ລິຂະສິດ (Copyright):</span> Copyright © {{ date('Y') }} FNS.NUOL. All rights reserved.</li>
                        </ul>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-gray-50 px-6 py-4 rounded-b-2xl border-t border-gray-200 flex justify-end">
                    <button @click="showAboutModal = false" class="w-full sm:w-auto px-6 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">
                        ປິດ
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registered successfully');
                    })
                    .catch(error => {
                        console.log('ServiceWorker registration failed: ', error);
                    });
            });
        }
    </script>
</body>
</html>