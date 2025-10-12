<div class="mt-8">
    <h3 class="text-lg font-medium border-b pb-2 mb-4">ປະຫວັດການດຳເນີນການ</h3>
    <div class="space-y-4">
        @forelse ($logs->sortByDesc('created_at') as $log)
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    {{-- ອາດຈະເພີ່ມຮູບ Profile ຕາມຫຼັງ --}}
                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center text-xs">
                        {{ substr($log->user->name, 0, 1) }}
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">
                        {{ $log->user->name }}
                        <span class="text-xs text-gray-500">({{ $log->user->role->name }})</span>
                    </p>
                    <p class="text-sm text-gray-700">{{ $log->action }}: {{ $log->comment }}</p>
                    <p class="text-xs text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">ຍັງບໍ່ມີປະຫວັດການດຳເນີນການ</p>
        @endforelse
    </div>
</div>