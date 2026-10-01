<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ເພີ່ມຜູ້ໃຊ້ໃໝ່') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <div class="p-4 sm:p-6 text-gray-900">
                    
                    {{-- Display Validation Errors --}}
                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
                            <strong class="font-bold">ເກີດຂໍ້ຜິດພາດ!</strong>
                            <ul class="mt-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('ຊື່ ແລະ ນາມສະກຸນ')" />
                                <x-text-input id="name" class="block mt-1 w-full text-sm" type="text" name="name" :value="old('name')" required autofocus />
                            </div>

                            <!-- Username -->
                            <div>
                                <x-input-label for="username" :value="__('ຊື່ຜູ້ໃຊ້ (Username)')" />
                                <x-text-input id="username" class="block mt-1 w-full text-sm" type="text" name="username" :value="old('username')" required />
                                <x-input-error :messages="$errors->get('username')" class="mt-1 text-xs" />
                            </div>

                            <!-- Email Address -->
                            <div class="md:col-span-2">
                                <x-input-label for="email" :value="__('ອີເມວ')" />
                                <x-text-input id="email" class="block mt-1 w-full text-sm" type="email" name="email" :value="old('email')" required />
                            </div>

                            <!-- Password -->
                            <div>
                                <x-input-label for="password" :value="__('ລະຫັດຜ່ານ')" />
                                <x-text-input id="password" class="block mt-1 w-full text-sm" type="password" name="password" required />
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <x-input-label for="password_confirmation" :value="__('ຢືນຢັນລະຫັດຜ່ານ')" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full text-sm" type="password" name="password_confirmation" required />
                            </div>

                            <!-- Role -->
                            <div>
                                <x-input-label for="role_id" :value="__('ບົດບາດ (Role)')" />
                                <select name="role_id" id="role_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm" required>
                                    <option value="">-- ເລືອກບົດບາດ --</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Department -->
                            <div>
                                <x-input-label for="department_id" :value="__('ພາກສ່ວນ (Department)')" />
                                <select name="department_id" id="department_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                                    <option value="">-- ບໍ່ມີພາກສ່ວນ (ເລືອກໄດ້) --</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Buttons Responsive -->
                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end mt-8 gap-3 sm:space-x-4 border-t pt-6">
                            <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto text-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-sm font-semibold text-gray-700 rounded-lg transition">
                                {{ __('ຍົກເລີກ') }}
                            </a>

                            <button type="submit" class="w-full sm:w-auto justify-center inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow-md transition">
                                {{ __('ບັນທຶກຜູ້ໃຊ້ໃໝ່') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>