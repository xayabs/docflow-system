<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ເອກະສານຂອງ ຝ່າຍຈັດຊື້/ສ້ອມແປງ') }} 
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg rounded-xl border border-gray-200">
                <div class="p-4 sm:p-6 text-gray-900">
                    
                    {{-- ຂໍ້ຄວາມແຈ້ງເຕືອນ --}}
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <h3 class="text-lg font-medium mb-4">ລາຍການເອກະສານທີ່ລໍຖ້າການດຳເນີນການ</h3>
                    
                    {{-- ຟອມກັ່ນຕອງຄົ້ນຫາ --}}
                    <x-document-filter-form 
                        :action="route('procurement.dashboard')" 
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
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ສະຖານະປະຈຸບັນ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ພາກສ່ວນສະເໜີ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ວັນທີສ້າງ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ພິມ</th> 
                                    <th class="relative px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">ການດໍາເນີນການ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($documents as $document)
                                    <tr class="hover:bg-gray-50 transition {{ $document->status == 'REJECTED' ? 'bg-red-50/30' : ($document->status == 'DRAFT' ? 'bg-gray-50' : '') }}">
                                        <td class="px-6 py-4 text-center font-mono text-sm text-gray-700">{{ $document->document_code ?? '-' }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $document->title }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                                {{ translateStatus($document->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $document->requester->department->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-center text-xs text-gray-500">{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 text-center">
                                            @if($document->document_type_id == 1 && $document->document_code)
                                                <a href="{{ route('procurement.documents.print', $document->id) }}" target="_blank" title="ພິມເອກະສານ" class="text-gray-500 hover:text-blue-600 inline-block transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                    </svg>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            
                                            {{-- Logic ການສະແດງປຸ່ມ Actions --}}
                                            @if(in_array($document->status, ['DRAFT', 'REJECTED']))
                                                @if($document->requester_id === auth()->id())
                                                    <a href="{{ route('procurement.documents.edit', $document->id) }}" class="text-blue-600 hover:text-blue-900 font-semibold px-2 py-1 rounded hover:bg-blue-50 transition">
                                                        ແກ້ໄຂ
                                                    </a>
                
                                                    @if($document->status === 'DRAFT')
                                                        <form action="{{ route('procurement.documents.submit', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານຕ້ອງການສົ່ງເອກະສານນີ້ແມ່ນບໍ່?')">
                                                            @csrf @method('PATCH')
                                                            <button type="submit" class="text-green-600 hover:text-green-900 font-semibold px-2 py-1 rounded hover:bg-green-50 transition">ສົ່ງ</button>
                                                        </form>
                                                        <form action="{{ route('procurement.documents.destroy', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບສະບັບຮ່າງນີ້?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold px-2 py-1 rounded hover:bg-red-50 transition">ລຶບ</button>
                                                        </form>
                                                    @endif
                                                @else
                                                    {{-- ກໍລະນີທີ່ເປັນເອກະສານຈັດຊື້ທີ່ຖືກ REJECTED (ຄົນສ້າງຄື Staff) --}}
                                                    <a href="{{ route('procurement.documents.show', $document->id) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 transition">
                                                        ກວດສອບ ແລະ ດຳເນີນການ
                                                    </a>
                                                @endif
                                            @else
                                                <a href="{{ route('procurement.documents.show', $document->id) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 transition">
                                                    @if($document->parent_document_id !== null)
                                                        ເບິ່ງລາຍລະອຽດ
                                                    @else
                                                        ກວດສອບ ແລະ ດຳເນີນການ
                                                    @endif
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                            ບໍ່ມີເອກະສານທີ່ລໍຖ້າການດຳເນີນການ
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
                            <div class="bg-white border rounded-xl p-4 shadow-sm relative overflow-hidden {{ $document->status == 'REJECTED' ? 'border-red-200 bg-red-50/20' : ($document->status == 'DRAFT' ? 'border-gray-300 bg-gray-50' : 'border-gray-200') }}">
                                
                                {{-- ແຖບສີຂ້າງຊ້າຍ (ປ່ຽນສີຕາມສະຖານະ) --}}
                                @php
                                    $borderColor = 'bg-blue-400';
                                    if($document->status == 'REJECTED') $borderColor = 'bg-red-400 opacity-80';
                                    if($document->status == 'DRAFT') $borderColor = 'bg-gray-400';
                                @endphp
                                <div class="absolute top-0 left-0 w-1.5 h-full {{ $borderColor }}"></div>

                                {{-- ແຖວເທິງສຸດ: ລະຫັດເອກະສານ & ປ້າຍສະຖານະ --}}
                                <div class="flex justify-between items-start pl-2 mb-2">
                                    <span class="font-mono text-xs font-bold text-gray-700 bg-white border border-gray-200 px-2 py-1 rounded shadow-sm">
                                        {{ $document->document_code ?? 'ສະບັບຮ່າງ' }}
                                    </span>
                                    <span class="px-2 py-0.5 inline-flex text-[11px] font-semibold rounded-full {{ getStatusColorClass($document->status) }} text-right max-w-[50%] truncate shadow-sm">
                                        {{ translateStatus($document->status) }}
                                    </span>
                                </div>

                                {{-- ສ່ວນກາງ: ຫົວຂໍ້ເອກະສານ --}}
                                <div class="pl-2 mb-3">
                                    <h4 class="font-bold text-gray-900 text-sm leading-snug">{{ $document->title }}</h4>
                                </div>

                                {{-- ກ່ອງລາຍລະອຽດຍ່ອຍ --}}
                                <div class="pl-2">
                                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 bg-white p-2.5 rounded-lg border {{ $document->status == 'REJECTED' ? 'border-red-100' : 'border-gray-100' }}">
                                        <div>
                                            <span class="block text-gray-400 mb-0.5">ວັນທີສ້າງ</span>
                                            <span class="font-medium">{{ $document->created_at->format('d/m/Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-gray-400 mb-0.5">ເວລາ</span>
                                            <span class="font-medium">{{ $document->created_at->format('H:i') }}</span>
                                        </div>
                                        <div class="col-span-2 mt-1 pt-1 border-t border-gray-100">
                                            <span class="block text-gray-400 mb-0.5">ພາກສ່ວນສະເໜີ</span>
                                            <span class="font-medium text-gray-800">{{ $document->requester->department->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- ແຖວລຸ່ມສຸດ: ປຸ່ມດຳເນີນການ --}}
                                <div class="pt-3 mt-3 border-t border-gray-100 pl-2 flex justify-between items-center">
                                    
                                    {{-- ປຸ່ມພິມ (ສະເພາະມືຖື) --}}
                                    <div>
                                        @if($document->document_type_id == 1 && $document->document_code)
                                            <a href="{{ route('procurement.documents.print', $document->id) }}" target="_blank" class="inline-flex items-center text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 px-2.5 py-1.5 rounded-lg border border-gray-300 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                                ພິມ
                                            </a>
                                        @endif
                                    </div>

                                    {{-- ກຸ່ມປຸ່ມ Actions --}}
                                    <div class="flex space-x-2">
                                        @if(in_array($document->status, ['DRAFT', 'REJECTED']) && $document->requester_id === auth()->id())
                                            <a href="{{ route('procurement.documents.edit', $document->id) }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200 transition">
                                                ແກ້ໄຂ
                                            </a>
                                            @if($document->status === 'DRAFT')
                                                <form action="{{ route('procurement.documents.submit', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານຕ້ອງການສົ່ງເອກະສານນີ້ແມ່ນບໍ່?')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center text-sm font-semibold text-white bg-green-600 hover:bg-green-700 px-3 py-1.5 rounded-lg transition shadow-sm">ສົ່ງ</button>
                                                </form>
                                            @endif
                                        @else
                                            <a href="{{ route('procurement.documents.show', $document->id) }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-200 shadow-sm transition">
                                                @if($document->parent_document_id !== null)
                                                    ເບິ່ງລາຍລະອຽດ
                                                @else
                                                    ກວດສອບ ແລະ ດຳເນີນການ
                                                @endif
                                            </a>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        @empty
                            <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300 text-gray-500">
                                ບໍ່ມີເອກະສານທີ່ລໍຖ້າການດຳເນີນການ
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-6">
                        {{ $documents->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>