<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ຈັດການພາກສ່ວນ (Departments)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Form for creating a new department -->
            <div class="md:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">ເພີ່ມພາກວິຊາ/ພະແນກໃໝ່</h3>
                        
                        @if ($errors->any())
                            {{-- ... (Error display code) ... --}}
                        @endif

                        <form action="{{ route('admin.departments.store') }}" method="POST">
                            @csrf
                            <div>
                                <x-input-label for="name" :value="__('ຊື່ພາກວິຊາ/ພະແນກ')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                            </div>
                            <div class="mt-4">
                                <x-primary-button>
                                    {{ __('ບັນທຶກ') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Table of existing departments -->
            <div class="md:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        @if (session('success'))
                            {{-- ... (Success message code) ... --}}
                        @endif

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ລະຫັດ</th>
                                    <th class="px-6 py-3 text-left text-base font-bold text-gray-500 uppercase">ຊື່ພາກສ່ວນ</th>
                                    <th class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($departments as $department)
                                    <tr>
                                        <td class="px-6 py-4 text-center">{{ $department->id }}</td>
                                        <td class="px-6 py-4">{{ $department->name }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('admin.departments.destroy', $department->id) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບພາກວິຊາ/ພະແນກນີ້?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">ລຶບ</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">ບໍ່ພົບຂໍ້ມູນພາກວິຊາ/ພະແນກ</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>