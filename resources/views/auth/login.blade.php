<x-guest-layout>
    <style>
        .custom-validation-bubble {
            position: absolute;
            background-color: #fee2e2; /* ສີແດງອ່ອນຫຼາຍ (bg-red-100) */
            border: 1px solid #fca5a5; /* ສີແດງອ່ອນ (border-red-300) */
            color: #dc2626; /* ສີແດງເຂັ້ມ (text-red-600) */
            border-radius: 0.375rem;
            padding: 0.75rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            z-index: 10;
            font-family: 'Saysettha OT', sans-serif;
            display: none;
        }
    </style>

    <x-slot name="title">
        {{ __('ເຂົ້າສູ່ລະບົບ') }} - {{ config('app.name', 'FDTS') }}
    </x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- ======================================================== --}}
    {{-- ຄອບດ້ວຍ x-data="{ showGuide: false }" ສຳລັບຄວບຄຸມ Pop-up Modal --}}
    {{-- ======================================================== --}}
    <div x-data="{ showGuide: false }">
        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <!-- Username -->
            <div class="relative">
                <x-input-label for="username" :value="__('ຊື່ຜູ້ໃຊ້')" />
                <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('username')" class="mt-2" />
                <div id="username_error" class="custom-validation-bubble">ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້ໃນຊ່ອງນີ້.</div>
            </div>

            <!-- Password -->
            <div class="mt-4 relative">
                <x-input-label for="password" :value="__('ລະຫັດຜ່ານ')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                <div id="password_error" class="custom-validation-bubble">ກະລຸນາປ້ອນລະຫັດຜ່ານໃນຊ່ອງນີ້.</div>
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('ຈື່ຂ້ອຍໄວ້ໃນລະບົບ') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('password.request') }}">
                        {{ __('ລືມລະຫັດຜ່ານບໍ?') }}
                    </a>
                @endif

                <x-primary-button class="ms-3 bg-blue-600 hover:bg-blue-700">
                    {{ __('ເຂົ້າສູ່ລະບົບ') }}
                </x-primary-button>
            </div>

            {{-- ======================================================== --}}
            {{-- ປຸ່ມແນະນຳວິທີນຳໃຊ້ລະບົບ (ວາງໄວ້ລຸ່ມສຸດຂອງຟອມ) --}}
            {{-- ======================================================== --}}
            <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                <button type="button" @click="showGuide = true" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    ແນະນຳວິທີນຳໃຊ້ລະບົບເບື້ອງຕົ້ນ
                </button>
            </div>
        </form>

        {{-- ======================================================== --}}
        {{-- Pop-up Modal ສະແດງຄູ່ມືແນະນຳ (ເປີດ/ປິດດ້ວຍ Alpine.js) --}}
        {{-- ======================================================== --}}
        <div x-show="showGuide" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                
                {{-- ພື້ນຫຼັງສີດຳຈາງໆ --}}
                <div x-show="showGuide" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showGuide = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- ກ່ອງເນື້ອໃນ Modal --}}
                <div x-show="showGuide" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-bold text-gray-900 border-b pb-3 mb-4 flex items-center" id="modal-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    ຍິນດີຕ້ອນຮັບສູ່ ລະບົບ FNS-FDTMS
                                </h3>
                                <div class="mt-2 text-sm text-gray-600 space-y-4">
                                    <p>
                                        <strong>ລະບົບຕິດຕາມເອກະສານການເງິນ (FNS-FDTMS)</strong> ຄະນະວິທະຍາສາດທຳມະຊາດ ຊ່ວຍໃຫ້ຂະບວນການອະນຸມັດເອກະສານພາຍໃນມີຄວາມວ່ອງໄວ, ໂປ່ງໃສ ແລະ ຕິດຕາມໄດ້ທຸກຂັ້ນຕອນ.
                                    </p>
                                    <ul class="list-disc pl-5 space-y-2">
                                        <li><strong class="text-gray-800">ການເຂົ້າສູ່ລະບົບ:</strong> ໃຊ້ <strong>ຊື່ຜູ້ໃຊ້ (Username)</strong> ແລະ ລະຫັດຜ່ານ ທີ່ໄດ້ຮັບການມອບໝາຍຈາກຜູ້ບໍລິຫານລະບົບ.</li>
                                        <li><strong class="text-blue-600">ສຳລັບພະນັກງານ/ອາຈານຜູ້ສະເໜີ (Staff):</strong> ສາມາດສ້າງເອກະສານ "ຂໍຖອນເງິນ" ຫຼື "ຂໍຈັດຊື້/ສ້ອມແປງ" ພ້ອມທັງຕິດຕາມສະຖານະເອກະສານຂອງທ່ານໄດ້ທັນທີວ່າກຳລັງຢູ່ຂັ້ນຕອນໃດ.</li>
                                        <li><strong class="text-indigo-600">ສຳລັບຜູ້ກວດສອບ ແລະ ຜູ້ບໍລິຫານ (Approvers):</strong> ເມື່ອມີເອກະສານສົ່ງມາຮອດຂັ້ນຕອນຂອງທ່ານ, ລະບົບຈະແຈ້ງເຕືອນ. ທ່ານສາມາດກົດອະນຸມັດ, ສົ່ງຕໍ່, ຫຼື ສົ່ງເອກະສານກັບຄືນພ້ອມເຫດຜົນໄດ້ທັນທີ.</li>
                                        <li><strong class="text-green-600">ຮອງຮັບທຸກອຸປະກອນ:</strong> ທ່ານສາມາດໃຊ້ງານຜ່ານຄອມພິວເຕີ ຫຼື ໂທລະສັບມືຖືໄດ້ຢ່າງສົມບູນ (ສາມາດກົດ <strong>Add to Home Screen</strong> ໃນມືຖືເພື່ອໃຊ້ງານຄືແອັບໄດ້).</li>
                                    </ul>
                                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 mt-4 text-xs text-blue-900">
                                        <p class="font-bold">💡 ພົບບັນຫາການໃຊ້ງານ ຫຼື ລືມລະຫັດຜ່ານ?</p>
                                        <p class="mt-0.5">ກະລຸນາຕິດຕໍ່ ຜູ້ບໍລິຫານລະບົບ (System Administrator) ຕາມເບີໂທ ວ໊ອດແອັບ ອີເມວ ຢູ່ໃນໜ້າກ່ຽວກັບໂປຣແກຣມ ເພື່ອຂໍຄວາມຊ່ວຍເຫຼືອ.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                        <button type="button" @click="showGuide = false" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-5 py-2 bg-blue-600 text-sm font-bold text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto transition-colors duration-200">
                            ເຂົ້າໃຈແລ້ວ, ປິດໜ້າຈໍນີ້
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Script ກວດສອບ Validation ເດີມຂອງທ່ານ --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector('form');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const usernameError = document.getElementById('username_error');
            const passwordError = document.getElementById('password_error');

            form.addEventListener('submit', function(event) {
                // ເຊື່ອງ Error ເກົ່າກ່ອນ
                usernameError.style.display = 'none';
                passwordError.style.display = 'none';

                // ກວດສອບເອງ
                let isValid = true;
                if (!usernameInput.value) {
                    usernameError.style.display = 'block';
                    isValid = false;
                }
                if (!passwordInput.value) {
                    passwordError.style.display = 'block';
                    isValid = false;
                }
                
                // ຖ້າບໍ່ຜ່ານ, ໃຫ້ຢຸດການສົ່ງຟອມ
                if (!isValid) {
                    event.preventDefault();
                }
            });

            // ເມື່ອ "ຄລິກ" ໃສ່ຊ່ອງ Username, ໃຫ້ເຊື່ອງ Error
            usernameInput.addEventListener('focus', function() {
                usernameError.style.display = 'none';
            });

            // ເມື່ອ "ຄລິກ" ໃສ່ຊ່ອງ Password, ໃຫ້ເຊື່ອງ Error
            passwordInput.addEventListener('focus', function() {
                passwordError.style.display = 'none';
            });
        });
    </script>
</x-guest-layout>