<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ຈັດການຜູ້ໃຊ້') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- ສະແດງຂໍ້ຄວາມແຈ້ງເຕືອນຫຼັງຈາກບັນທຶກສຳເລັດ --}}
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    
                    {{-- === ສະແດງຂໍ້ຄວາມ Error (ຖ້າມີ) === --}}
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- ===== ເພີ່ມໂຄດຟອມຄົ້ນຫາ ແລະ ກັ່ນຕອງໃສ່ບ່ອນນີ້ ===== --}}
                    {{-- ========================================================== --}}
                    <div class="mb-4 border-b border-gray-200 pb-4">
                        <form action="{{ route('admin.users.index') }}" method="GET">
                            <div class="flex flex-col md:flex-row md:items-end md:space-x-4">
                                <!-- Search Input -->
                                <div class="flex-grow">
                                    <label for="search" class="block text-sm font-medium text-gray-700">ຄົ້ນຫາ (ຊື່ ຫຼື ອີເມວ)</label>
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="ພິມຄຳຄົ້ນຫາ...">
                                </div>
                                <!-- Role Filter -->
                                <div>
                                    <label for="role_id" class="block text-sm font-medium text-gray-700">ກັ່ນຕອງຕາມບົດບາດ</label>
                                    <select name="role_id" id="role_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">-- ທຸກບົດບາດ --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" @selected(request('role_id') == $role->id)>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Buttons -->
                                <div class="mt-4 md:mt-0">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        ຄົ້ນຫາ
                                    </button>
                                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500 ms-2">
                                        ລ້າງ
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    {{-- ========================================================== --}}

                    {{-- ປຸ່ມເພີ່ມຜູ້ໃຊ້ໃໝ່ --}}
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('admin.users.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-sans font-bold py-2 px-4 rounded">
                            + ເພີ່ມຜູ້ໃຊ້ໃໝ່
                        </a>
                    </div>

                    {{-- ຕາຕະລາງສະແດງລາຍຊື່ຜູ້ໃຊ້ --}}
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-base font-bold text-gray-500 uppercase tracking-wider">ຊື່ບົດບາດ</th>
                                <th class="px-6 py-3 text-left text-base font-bold text-gray-500 uppercase tracking-wider">ຊື່ຜູ້ໃຊ້</th>
                                <th scope="col" class="px-6 py-3 text-left ttext-base font-bold text-gray-500 uppercase tracking-wider">ອີເມວ</th>
                                <th scope="col" class="px-6 py-3 text-left text-base font-bold text-gray-500 uppercase tracking-wider">ບົດບາດ (Role)</th>
                                <th scope="col" class="px-6 py-3 text-left text-base font-bold text-gray-500 uppercase tracking-wider">ພາກສ່ວນ (Department)</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">ແກ້ໄຂ</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->username }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->role->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->department->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900">ແກ້ໄຂ</a>
                                        <form class="inline-block" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບຜູ້ໃຊ້ນີ້?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 ms-2">ລຶບ</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        ບໍ່ພົບຂໍ້ມູນຜູ້ໃຊ້
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- ລິ້ງສຳລັບການແບ່ງໜ້າ --}}
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>