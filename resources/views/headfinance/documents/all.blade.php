<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ຕິດຕາມເອກະສານທັງໝົດໃນລະບົບ') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg rounded-xl border border-gray-200">
                <div class="p-4 sm:p-6 text-gray-900">
                    
                    {{-- ຟອມກັ່ນຕອງຄົ້ນຫາ --}}
                    <x-document-filter-form 
                        :action="route('headfinance.documents.all')" 
                        :departments="$departments"
                        :statuses="$statuses"
                        title-span="md:col-span-2" 
                    />

                    {{-- ========================================================================= --}}
                    {{-- 1. ສ່ວນສະແດງຜົນເທິງຈໍຄອມພິວເຕີ (Desktop Table: hidden md:block) --}}
                    {{-- ========================================================================= --}}
                    <div class="hidden md:block overflow-x-auto mt-4">
                        <table class="min-w-full divide-y divide-gray-200 border-b border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ລະຫັດເອກະສານ</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ຫົວຂໍ້ເອກະສານ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ຜູ້ຮ້ອງຂໍ</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">ມູນຄ່າລວມ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ສະຖານະປະຈຸບັນ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ວັນທີສົ່ງ</th>
                                    <th class="relative px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ການດໍາເນີນການ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($documents as $document)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 text-center font-mono text-sm text-gray-700">{{ $document->document_code ?? '-' }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $document->title }}</td>
                                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $document->requester->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-semibold text-blue-600">
                                            {{ $document->document_type_id == 2 ? 'N/A' : number_format($document->total_amount, 0) . ' KIP' }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                                {{ translateStatus($document->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-xs text-gray-500">{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('headfinance.documents.show', $document->id) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 transition">
                                                ເບິ່ງລາຍລະອຽດ
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                            ບໍ່ມີເອກະສານໃນລະບົບ
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- 2. ສ່ວນສະແດງຜົນເທິງມືຖື (Mobile Card View: block md:hidden)                   --}}
                    {{-- ========================================================================= --}}
                    <div class="block md:hidden space-y-4 mt-4">
                        @forelse ($documents as $document)
                            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm relative overflow-hidden">
                                
                                {{-- ແຖບສີຂ້າງຊ້າຍ --}}
                                <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-400 opacity-70"></div>

                                {{-- ແຖວເທິງສຸດ: ລະຫັດເອກະສານ & ປ້າຍສະຖານະ --}}
                                <div class="flex justify-between items-start pl-2 mb-2">
                                    <span class="font-mono text-xs font-bold text-gray-700 bg-gray-100 border border-gray-200 px-2 py-1 rounded">
                                        {{ $document->document_code ?? '-' }}
                                    </span>
                                    <span class="px-2 py-0.5 inline-flex text-[11px] font-semibold rounded-full {{ getStatusColorClass($document->status) }} text-right max-w-[50%] truncate">
                                        {{ translateStatus($document->status) }}
                                    </span>
                                </div>

                                {{-- ສ່ວນກາງ: ຫົວຂໍ້ເອກະສານ --}}
                                <div class="pl-2 mb-3">
                                    <h4 class="font-bold text-gray-900 text-sm leading-snug">{{ $document->title }}</h4>
                                </div>

                                {{-- ກ່ອງລາຍລະອຽດຍ່ອຍ --}}
                                <div class="pl-2">
                                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                        <div>
                                            <span class="block text-gray-400 mb-0.5">ຜູ້ຮ້ອງຂໍ</span>
                                            <span class="font-medium text-gray-800">{{ $document->requester->name ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-gray-400 mb-0.5">ມູນຄ່າລວມ</span>
                                            <span class="font-bold text-blue-600">
                                                {{ $document->document_type_id == 2 ? 'N/A' : number_format($document->total_amount, 0) . ' KIP' }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 mt-1 pt-1 border-t border-gray-200 flex justify-between">
                                            <span class="text-gray-400">ວັນທີ: <span class="font-medium text-gray-700">{{ $document->created_at->format('d/m/Y H:i') }}</span></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- ແຖວລຸ່ມສຸດ: ປຸ່ມເບິ່ງລາຍລະອຽດ --}}
                                <div class="pt-3 mt-3 border-t border-gray-100 pl-2 flex justify-end">
                                    <a href="{{ route('headfinance.documents.show', $document->id) }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                        ເບິ່ງລາຍລະອຽດ
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </a>
                                </div>

                            </div>
                        @empty
                            <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300 text-gray-500">
                                ບໍ່ມີເອກະສານໃນລະບົບ
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