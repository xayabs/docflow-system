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
    {{-- เราจะใช้ฟอนต์ Noto Sans Lao จาก Google Fonts เพื่อความสวยงาม --}}
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;500;700&display=swap" rel="stylesheet">

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
        <div class="fixed top-0 left-0 right-0 z-10 flex justify-between items-center p-6">
            <div>
                <a href="{{ asset('user_manual.pdf') }}" target="_blank" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                    ຄູ່ມືການນໍາໃຊ້
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="#" @click.prevent="showAboutModal = true" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                    ກ່ຽວກັບໂປຣແກຣມ
                </a>
            </div>
        </div>
        {{-- ส่วนของปุ่ม Login/Register ที่มุมขวาบน --}}
        @if (Route::has('login'))
            <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                @auth
                    <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"><!--Dashboard--></a>
                @else
                    <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"></a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"></a>
                    @endif
                @endauth
            </div>
        @endif
        
        {{-- ส่วน Hero Section ที่เราออกแบบ --}}
        <div class="max-w-7xl mx-auto p-6 lg:p-8">
            <div class="flex justify-center">
                {{-- ใส่โลโก้ของคุณที่นี่ --}}
                <img src="{{ asset('images/nuol_logo.png') }}" alt="NUOL Logo" class="h-80 w-auto">
            </div>

            <div class="mt-8 text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
                    ລະບົບຕິດຕາມເອກະສານການເງິນ
                </h1>
                
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    ຄະນະວິທະຍາສາດທຳມະຊາດ | ມະຫາວິທະຍາໄລແຫ່ງຊາດ
                </p>
                
                <p class="mt-2 text-md text-gray-500 dark:text-gray-500">
                    ຕິດຕາມສະຖານະ, ເພີ່ມຄວາມໂປ່ງໃສ, ຫຼຸດຜ່ອນການນຳໃຊ້ເຈ້ຍ.
                </p>
            </div>
            
            {{-- ปุ่ม Call to Action --}}
            <div class="mt-12 flex justify-center">
                <a href="{{ route('login') }}" class="px-8 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-lg text-white hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    ເຂົ້າສູ່ລະບົບ
                </a>
            </div>
            <div class="mt-16 flex justify-center">
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    © {{ date('Y') }} ຄະນະວິທະຍາສາດທຳມະຊາດ | ມະຫາວິທະຍາໄລແຫ່ງຊາດ
                </p>
            </div>
        </div>
        {{-- ========================================================== --}}
        {{-- ===== 2. เพิ่มโค้ด Modal ทั้งหมดไว้ที่นี่ (นอก div หลัก) ===== --}}
        {{-- ========================================================== --}}
        <div x-show="showAboutModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center"
            style="display: none;"> {{-- style="display: none;" ช่วยป้องกันการกระพริบตอนโหลดหน้า --}}

            <!-- Background Overlay -->
            <div @click="showAboutModal = false" class="fixed inset-0 bg-black opacity-50"></div>

            <!-- Modal Content -->
            <div @click.stop class="bg-white rounded-lg shadow-xl p-6 md:p-8 max-w-2xl w-full mx-4 z-10">
            
                <h2 class="text-2xl font-bold mb-4">ກ່ຽວກັບລະບົບ</h2>
            
                <div class="space-y-4 text-gray-700">
                    <div class="pt-4">
                        <h3 class="text-lg font-semibold">1. ຂໍ້ມູນພື້ນຖານຂອງໂປຣແກຣມ (Basic Information):</h3>
        
                        {{-- ใช้ pl-8 (padding-left: 2rem) เพื่อย่อหน้าเข้าไป --}}
                        <ul class="list-disc list-inside mt-2 pl-8 space-y-2">
                            <li>
                                <span class="font-semibold">ຊື່ໂປຣແກຣມ (Program Name):</span> ລະບົບຕິດຕາມເອກະສານການເງິນ
                            </li>
                            <li>
                                <span class="font-semibold">ຮຸ່ນ (Version):</span> {{ config('app_settings.version') }}
                            </li>
                            <li>
                                <span class="font-semibold">ຄຳອະທິບາຍโดยຫຍໍ້ (Brief Description):</span>
                                {{-- ใช้ blockquote หรือ div ที่มี padding เพื่อย่อหน้าคำอธิบาย --}}
                                <div class="pl-4 mt-1 border-l-2 border-gray-200">
                                    <p>ໂປຣແກຣມນີ້ຖືກພັດທະນາຂຶ້ນ ເພື່ອເພີ່ມປະສິດທິພາບໃນການບໍລິຫານຈັດການເອກະສານທາງດ້ານການເງິນຂອງຄະນະວິທະຍາສາດທໍາມະຊາດ. ລະບົບຈະຊ່ວຍໃຫ້ສາມາດຕິດຕາມຂັ້ນຕອນ ແລະ ການເຄື່ອນໄຫວຂອງເອກະສານໄດ້ຢ່າງເປັນລະບົບ, ຫຼຸດຜ່ອນການສູນຫາຍ, ສ້າງຄວາມໂປ່ງໃສ ແລະ ຊ່ວຍໃຫ້ການຄົ້ນຫາຂໍ້ມູນໃນອະດີດສາມາດເຮັດໄດ້ຢ່າງງ່າຍດາຍ.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                
                    <div class="pt-4 border-t">
                        <h3 class="text-lg font-semibold">2. ຂໍ້ມູນຜູ້ພັດທະນາ (Developer Information)</h3>
        
                        <ul class="list-disc list-inside mt-2 pl-8 space-y-2">
                            <li>
                                <span class="font-semibold">ຊື່ຜູ້ພັດທະນາ (Developer Name):</span> ອາຈານ ບົວສົດ ໄຊຍະຈັກ
                            </li>
                            <li>
                                <span class="font-semibold">ຂໍ້ມູນຕິດຕໍ່ (Contact Information):</span>
                                {{-- ใช้ ul ซ้อนเข้าไปอีกชั้นเพื่อย่อหน้า --}}
                                <ul class="list-none mt-1 pl-4">
                                    <li>- M/W/L: +(856) 20 22245134</li>
                                    <li>- E-Mail: bouasoth@nuol.edu.la</li>
                                </ul>
                            </li>
                            <li>
                                <span class="font-semibold">ລິຂະສິດ (Copyright):</span> Copyright © 2025 FNS.NUOL. All rights reserved.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Close Button -->
                <div class="mt-6 text-right">
                    <button @click="showAboutModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        ປິດ
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>