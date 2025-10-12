<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('ການແຈ້ງເຕືອນທັງໝົດ') }}
            </h2>
            {{-- ปุ่ม Mark all as read --}}
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 hover:underline">
                        ເຮັດເຄື່ອງໝາຍວ່າອ່ານແລ້ວທັງໝົດ
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('success'))
                        <div class="bg-green-100 border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        @forelse ($notifications as $notification)
                            <a href="{{ route('notifications.read', $notification->id) }}"
                               class="block p-4 rounded-lg transition duration-150 ease-in-out
                                      {{ $notification->read_at ? 'bg-white hover:bg-gray-50' : 'bg-blue-50 hover:bg-blue-100' }}">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 pt-1">
                                        {{-- ໄອຄອນ --}}
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900 {{ $notification->read_at ? '' : 'font-bold' }}">
                                            {{ $notification->data['message'] }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            ກ່ຽວກັບເອກະສານ: {{ $notification->data['title'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-center text-gray-500">ບໍ່ມີການແຈ້ງເຕືອນ</p>
                        @endforelse
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>