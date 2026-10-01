<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ຈັດການຜູ້ໃຊ້') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg rounded-xl border border-gray-200">
                <div class="p-4 sm:p-6 text-gray-900">
                    
                    {{-- Flash Messages --}}
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-sm mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Search & Filter Section --}}
                    <div class="mb-6 border-b border-gray-200 pb-5">
                        <form action="{{ route('admin.users.index') }}" method="GET">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-end">
                                <!-- Search Input -->
                                <div class="md:col-span-6">
                                    <x-input-label for="search" value="ຄົ້ນຫາ (ຊື່, ຊື່ຜູ້ໃຊ້ ຫຼື ອີເມວ)" />
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm" placeholder="ພິມຄຳຄົ້ນຫາ...">
                                </div>
                                <!-- Role Filter -->
                                <div class="md:col-span-3">
                                    <x-input-label for="role_id" value="ກັ່ນຕອງຕາມບົດບາດ" />
                                    <select name="role_id" id="role_id" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm">
                                        <option value="">-- ທຸກບົດບາດ --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" @selected(request('role_id') == $role->id)>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Buttons -->
                                <div class="md:col-span-3 flex space-x-2">
                                    <button type="submit" class="flex-1 inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 text-white font-bold text-xs rounded-lg hover:bg-blue-700 transition shadow-sm">
                                        ຄົ້ນຫາ
                                    </button>
                                    <a href="{{ route('admin.users.index') }}" class="inline-flex justify-center items-center px-4 py-2.5 bg-gray-100 text-gray-700 font-semibold text-xs rounded-lg hover:bg-gray-200 border border-gray-300 transition">
                                        ລ້າງ
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Add User Button --}}
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">ລາຍຊື່ຜູ້ໃຊ້ທັງໝົດ</h3>
                        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                            + ເພີ່ມຜູ້ໃຊ້ໃໝ່
                        </a>
                    </div>

                    {{-- 1. Desktop Table (hidden md:block) --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border-b border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ຊື່ ແລະ ນາມສະກຸນ</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ຊື່ຜູ້ໃຊ້</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ອີເມວ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ບົດບາດ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ພາກສ່ວນ</th>
                                    <th class="relative px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ການດຳເນີນການ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $user->name }}</td>
                                        <td class="px-6 py-4 font-mono text-sm text-gray-600">{{ $user->username }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ $user->role->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $user->department->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold px-2 py-1 rounded hover:bg-indigo-50 transition">ແກ້ໄຂ</a>
                                            <form class="inline-block" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບຜູ້ໃຊ້ນີ້?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-semibold px-2 py-1 rounded hover:bg-red-50 transition">ລຶບ</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">ບໍ່ພົບຂໍ້ມູນຜູ້ໃຊ້</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- 2. Mobile Cards View (block md:hidden) --}}
                    <div class="block md:hidden space-y-3">
                        @forelse ($users as $user)
                            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm space-y-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-sm">{{ $user->name }}</h4>
                                        <p class="text-xs font-mono text-gray-500">@<span>{{ $user->username }}</span></p>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $user->role->name ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg border border-gray-100 space-y-1">
                                    <p><span class="text-gray-400">ອີເມວ:</span> {{ $user->email }}</p>
                                    <p><span class="text-gray-400">ພາກສ່ວນ:</span> {{ $user->department->name ?? 'N/A' }}</p>
                                </div>

                                <div class="pt-2 border-t border-gray-100 flex justify-end space-x-2 text-xs font-semibold">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        ແກ້ໄຂ
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບຜູ້ໃຊ້ນີ້?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200">
                                            ລຶບ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-sm text-gray-500 bg-gray-50 rounded-xl border border-dashed">
                                ບໍ່ພົບຂໍ້ມູນຜູ້ໃຊ້
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $users->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>