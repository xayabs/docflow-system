<x-guest-layout>
    <style>
    .custom-validation-bubble {
        position: absolute;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        padding: 0.75rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        z-index: 10;
        font-family: 'Saysettha OT', sans-serif;
        display: none;
        
        /* ===== เพิ่มส่วนนี้ ===== */
        color: #dc2626; /* สีแดงเข้ม (text-red-600) */
        border-color: #fca5a5; /* สีแดงอ่อน (border-red-300) */
        background-color: #fee2e2; /* สีแดงอ่อนมาก (bg-red-100) */
    }
</style>

    <x-slot name="title">
        {{ __('ເຂົ້າສູ່ລະບົບ') }} - {{ config('app.name', 'FDTS') }}
    </x-slot>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <!-- Email Address --><!--
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>-->
        <!-- Username -->
        <div>
            <x-input-label for="username" :value="__('ຊື່ຜູ້ໃຊ້')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
            <div id="username_error" class="custom-validation-bubble">ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້ໃນຊ່ອງນີ້.</div>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

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
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector('form');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const usernameError = document.getElementById('username_error');
            const passwordError = document.getElementById('password_error');

            form.addEventListener('submit', function(event) {
                // ซ่อน Error เก่าก่อน
                usernameError.style.display = 'none';
                passwordError.style.display = 'none';

                // ตรวจสอบเอง
                let isValid = true;
                if (!usernameInput.value) {
                    usernameError.style.display = 'block';
                    isValid = false;
                }
                if (!passwordInput.value) {
                    passwordError.style.display = 'block';
                    isValid = false;
                }
                
                // ถ้าไม่ผ่าน, ให้หยุดการส่งฟอร์ม
                if (!isValid) {
                    event.preventDefault();
                }
            });

            // เมื่อ "คลิก" ที่ช่อง Username, ให้ซ่อน Error
            usernameInput.addEventListener('focus', function() {
                usernameError.style.display = 'none';
            });

            // เมื่อ "คลิก" ที่ช่อง Password, ให้ซ่อน Error
            passwordInput.addEventListener('focus', function() {
                passwordError.style.display = 'none';
            });
        });
    </script>

    <script>
        // รอให้หน้าเว็บโหลดเสร็จก่อน
        /*
        document.addEventListener("DOMContentLoaded", function() {
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const customMessage = 'ກະລຸນາປ້ອນຂໍ້ມູນໃນຊ່ອງນີ້.';

            // กำหนดข้อความ Error สำหรับช่อง Username
            usernameInput.addEventListener('invalid', function(event) {
                if (event.target.validity.valueMissing) {
                    event.target.setCustomValidity(customMessage);
                }
            });
            // ล้างข้อความ Error เมื่อผู้ใช้เริ่มพิมพ์
            usernameInput.addEventListener('input', function(event) {
                event.target.setCustomValidity('');
            });

            // กำหนดข้อความ Error สำหรับช่อง Password
            passwordInput.addEventListener('invalid', function(event) {
                if (event.target.validity.valueMissing) {
                    event.target.setCustomValidity(customMessage);
                }
            });
            passwordInput.addEventListener('input', function(event) {
                event.target.setCustomValidity('');
            });
        });*/
    </script>
</x-guest-layout>
