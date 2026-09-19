<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ປະວັດເອກະສານທີ່ໄດ້ປັບປຸງຄືນໃໝ່') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- ຟອມຄົ້ນຫາ ແລະ ກັ່ນຕອງ --}}
                    <x-document-filter-form 
                        :action="route('staff.history.rejected')" 
                        title-span="md:col-span-4"
                    />

                    {{-- ========================================================================= --}}
                    {{-- 1. ສ່ວນສະແດງຜົນເທິງຈໍຄອມ (Desktop Table: hidden md:block)                   --}}
                    {{-- ========================================================================= --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ລະຫັດເອກະສານ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຫົວຂໍ້ເອກະສານ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ປະເພດ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ສະຖານະ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ວັນທີສົ່ງ</th>
                                    <th class="relative px-6 py-3 text-center text-base font-bold">ການດໍາເນີນການ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($documents as $document)
                                    <tr class="bg-red-50/40 hover:bg-red-50/70 transition">
                                        <td class="px-6 py-4 text-center font-mono text-sm">{{ $document->document_code ?? '-' }}</td>
                                        <td class="px-6 py-4 font-medium">{{ $document->title }}</td>
                                        <td class="px-6 py-4 text-center">{{ $document->documentType->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                                {{ translateStatus($document->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-xs text-gray-500">{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('staff.documents.show', $document->id) }}" class="text-indigo-600 hover:text-indigo-900">ເບິ່ງລາຍລະອຽດ</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            ບໍ່ມີປະວັດເອກະສານທີ່ໃຫ້ໄປປັບປຸງຄືນ
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- 2. ສ່ວນສະແດງຜົນເທິງມືຖື (Mobile Card View: block md:hidden)                   --}}
                    {{-- ========================================================================= --}}
                    <div class="block md:hidden space-y-4">
                        @forelse ($documents as $document)
                            <div class="bg-white border border-red-200 bg-red-50/30 rounded-xl p-4 shadow-sm space-y-3">
                                
                                {{-- ແຖວເທິງສຸດ: ລະຫັດເອກະສານ & ປ້າຍສະຖານະ --}}
                                <div class="flex justify-between items-center">
                                    <span class="font-mono text-xs font-bold text-gray-700 bg-gray-200 px-2 py-1 rounded">
                                        {{ $document->document_code ?? 'ສະບັບຮ່າງ' }}
                                    </span>
                                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                        {{ translateStatus($document->status) }}
                                    </span>
                                </div>

                                {{-- ສ່ວນກາງ: ຫົວຂໍ້ເອກະສານ, ປະເພດ, ວັນທີ --}}
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-base leading-snug">{{ $document->title }}</h4>
                                    <div class="mt-2 text-xs text-gray-500 space-y-1">
                                        <p><span class="text-gray-400">ປະເພດ:</span> {{ $document->documentType->name ?? 'N/A' }}</p>
                                        <p><span class="text-gray-400">ວັນທີສົ່ງ:</span> {{ $document->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>

                                {{-- ແຖວລຸ່ມສຸດ: ປຸ່ມເບິ່ງລາຍລະອຽດ --}}
                                <div class="pt-3 border-t border-red-100 flex justify-end items-center text-sm">
                                    <a href="{{ route('staff.documents.show', $document->id) }}" class="text-indigo-600 font-semibold hover:text-indigo-900">
                                        ເບິ່ງລາຍລະອຽດ &rarr;
                                    </a>
                                </div>

                            </div>
                        @empty
                            <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed text-gray-500">
                                ບໍ່ມີປະວັດເອກະສານທີ່ໃຫ້ໄປປັບປຸງຄືນ
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4">
                        {{ $documents->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>