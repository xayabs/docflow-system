<x-app-layout>
    <x-slot name="header">
        {{-- ຫົວຂໍ້ໜ້າ: ສະແດງຊື່ຂອງຜູ້ໃຊ້ທີ່ກຳລັງແກ້ໄຂ --}}
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ແກ້ໄຂຂໍ້ມູນຜູ້ໃຊ້: ') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- ສະແດງຂໍ້ຄວາມ Error ຈາກ Validation --}}
                    @if ($errors->any())
                        <div class="mb-4">
                            <ul class="list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ສັງເກດ action ແລະ method --}}
                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                        @csrf
                        @method('PATCH') {{-- <-- ສຳຄັນ: ບອກ Laravel ວ່າເຮົາກຳລັງจะ Update ຂໍ້ມູນ --}}

                        <!-- Name -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('ຊື່ບົດບາດ')" />
                            {{-- ສະແດງຂໍ້ມູນເກົ່າຂອງ user --}}
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                        </div>

                        <!-- Username -->
                        <div class="mt-4">
                            <x-input-label for="username" :value="__('ຊື່ຜູ້ໃຊ້')" />
                            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username', $user->username)" required />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('ອີເມວ')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('ລະຫັດຜ່ານໃໝ່ (ປະຫວ່າງໄວ້ຖ້າບໍ່ຕ້ອງການປ່ຽນ)')" />
                            {{-- Password ບໍ່ຕ້ອງສະແດງຄ່າເກົ່າ ແລະ ບໍ່ required --}}
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('ຢືນຢັນລະຫັດຜ່ານໃໝ່')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                        </div>

                        <!-- Role -->
                        <div class="mt-4">
                            <x-input-label for="role_id" :value="__('ບົດບາດ (Role)')" />
                            <select name="role_id" id="role_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach ($roles as $role)
                                    {{-- ເລືອກ option ທີ່ຖືກຕ້ອງຕາມຂໍ້ມູນເກົ່າຂອງ user --}}
                                    <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department -->
                        <div class="mt-4">
                            <x-input-label for="department_id" :value="__('ພາກສ່ວນ (Department)')" />
                            <select name="department_id" id="department_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- ບໍ່ມີພາກສ່ວນ --</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id) == $department->id)>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('ຍົກເລີກ') }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('ອັບເດດຂໍ້ມູນ') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-app-layout>
 