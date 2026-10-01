<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ຈັດການປະເພດເອກະສານ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Form for creating a new document type -->
            <div class="md:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">ເພີ່ມປະເພດເອກະສານໃໝ່</h3>
                        
                        @if ($errors->any())
                            {{-- ... (Error display code) ... --}}
                        @endif

                        <form action="{{ route('admin.document-types.store') }}" method="POST">
                            @csrf
                            <div>
                                <x-input-label for="name" :value="__('ຊື່ປະເພດເອກະສານ')" />
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

            <!-- Table of existing document types -->
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
                                    <th class="px-6 py-3 text-left text-base font-bold text-gray-500 uppercase">ຊື່ປະເພດເອກະສານ</th>
                                    <th class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($documentTypes as $type)
                                    <tr>
                                        <td class="px-6 py-4 text-center">{{ $type->id }}</td>
                                        <td class="px-6 py-4">{{ $type->name }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('admin.document-types.destroy', $type->id) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບປະເພດເອກະສານນີ້?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">ລຶບ</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">ບໍ່ພົບຂໍ້ມູນປະເພດເອກະສານ</td>
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