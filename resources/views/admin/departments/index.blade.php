<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ຈັດການພາກສ່ວນ (Departments)') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Form for creating a new department -->
                <div class="md:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                        <div class="p-4 sm:p-6">
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">ເພີ່ມພາກວິຊາ/ພະແນກໃໝ່</h3>
                            
                            @if ($errors->any())
                                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
                                    <ul class="list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('admin.departments.store') }}" method="POST">
                                @csrf
                                <div>
                                    <x-input-label for="name" :value="__('ຊື່ພາກວິຊາ/ພະແນກ')" />
                                    <x-text-input id="name" class="block mt-1 w-full text-sm" type="text" name="name" :value="old('name')" placeholder="ພິມຊື່ພາກສ່ວນ..." required />
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-sm transition">
                                        {{ __('ບັນທຶກ') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Existing departments -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                        <div class="p-4 sm:p-6 text-gray-900">
                            @if (session('success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-sm mb-4">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">ລາຍການພາກສ່ວນທັງໝົດ</h3>

                            {{-- Desktop Table (hidden md:block) --}}
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ລະຫັດ</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ຊື່ພາກສ່ວນ</th>
                                            <th class="relative px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ການດຳເນີນການ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($departments as $department)
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-6 py-4 text-center font-mono text-sm text-gray-500">{{ $department->id }}</td>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $department->name }}</td>
                                                <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                                    <form action="{{ route('admin.departments.destroy', $department->id) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບພາກວິຊາ/ພະແນກນີ້?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 px-2 py-1 rounded hover:bg-red-50 transition">ລຶບ</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">ບໍ່ພົບຂໍ້ມູນພາກວິຊາ/ພະແນກ</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Mobile List View (block md:hidden) --}}
                            <div class="block md:hidden space-y-3">
                                @forelse ($departments as $department)
                                    <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl border border-gray-200 shadow-sm">
                                        <div class="flex items-center space-x-3">
                                            <span class="font-mono text-xs font-bold bg-white border border-gray-300 text-gray-600 px-2 py-1 rounded-md">#{{ $department->id }}</span>
                                            <span class="text-sm font-semibold text-gray-900 leading-snug">{{ $department->name }}</span>
                                        </div>
                                        <form action="{{ route('admin.departments.destroy', $department->id) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບພາກວິຊາ/ພະແນກນີ້?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 font-semibold text-xs px-2.5 py-1.5 rounded-lg border border-red-200 bg-white hover:bg-red-50 transition">
                                                ລຶບ
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-sm text-gray-500 bg-gray-50 rounded-xl border border-dashed">
                                        ບໍ່ພົບຂໍ້ມູນພາກວິຊາ/ພະແນກ
                                    </div>
                                @endforelse
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>