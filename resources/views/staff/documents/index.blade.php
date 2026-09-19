<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ເອກະສານຂອງ') }} {{ $departmentName }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- ປຸ່ມສ້າງເອກະສານໃໝ່ --}}
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('staff.documents.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            + ສ້າງເອກະສານໃໝ່
                        </a>
                    </div>

                    {{-- ຟອມຄົ້ນຫາ ແລະ ກັ່ນຕອງ --}}
                    <x-document-filter-form 
                        :action="route('staff.documents.index')" 
                        :statuses="$statuses"
                        title-span="md:col-span-3"
                    />

                    {{-- ========================================================================= --}}
                    {{-- 1. ສ່ວນສະແດງຜົນເທິງຈໍຄອມ (Desktop: hidden md:block)                         --}}
                    {{-- ========================================================================= --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ລະຫັດເອກະສານ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຫົວຂໍ້ເອກະສານ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ປະເພດ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ສະຖານະ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ວັນທີສ້າງ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ພີມ</th>
                                    <th class="relative px-6 py-3 text-center text-base font-bold">ການດໍາເນີນການ</th>
                                </tr>
                            </thead>
                            
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($documents as $document)
                                    <tr class="{{ $document->status == 'DRAFT' ? 'bg-gray-50' : ($document->status == 'REJECTED' ? 'bg-red-50' : '') }}">
                                        <td class="px-6 py-4 font-mono text-sm text-center">{{ $document->document_code ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $document->title }}</td>
                                        <td class="px-6 py-4 text-center">{{ $document->documentType->name ?? 'N/A' }}</td>

                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                                {{ translateStatus($document->status) }}
                                            </span>
                                        </td>
                    
                                        <td class="px-6 py-4 text-center">{{ $document->created_at->format('d/m/Y H:i') }}</td>
                    
                                        <td class="px-6 py-4 text-center">
                                            @if($document->document_code)
                                                <a href="{{ route('staff.documents.print', $document->id) }}" target="_blank" title="ພີມເອກະສານ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 hover:text-blue-600 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                    </svg>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                            @if(in_array($document->status, ['DRAFT', 'REJECTED']))
                                                <a href="{{ route('staff.documents.edit', $document->id) }}" class="text-blue-600 hover:text-blue-900 font-semibold">
                                                    ແກ້ໄຂ
                                                </a>
                                            @else
                                                <a href="{{ route('staff.documents.show', $document->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    ເບິ່ງລາຍລະອຽດ
                                                </a>
                                            @endif

                                            @if($document->status === 'DRAFT')
                                                <form action="{{ route('staff.documents.submit', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານຕ້ອງການສົ່ງເອກະສານສະບັບນີ້ແມ່ນບໍ?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-600 hover:text-green-900 font-semibold">ສົ່ງ</button>
                                                </form>

                                                <form action="{{ route('staff.documents.destroy', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານແນ່ໃນບໍວ່າຕ້ອງການລຶບເອກະສານສະບັບຮ່າງນີ້?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">ລຶບ</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            ທ່ານບໍ່ມີເອກະສານ
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
                            <div class="bg-white border rounded-xl p-4 shadow-sm space-y-3 {{ $document->status == 'DRAFT' ? 'border-gray-300 bg-gray-50' : ($document->status == 'REJECTED' ? 'border-red-300 bg-red-50/50' : 'border-gray-200') }}">
                                
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
                                        <p><span class="text-gray-400">ວັນທີສ້າງ:</span> {{ $document->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>

                                {{-- ແຖວລຸ່ມສຸດ: ປຸ່ມພິມ & ປຸ່ມດຳເນີນການ --}}
                                <div class="pt-3 border-t border-gray-100 flex justify-between items-center text-sm">
                                    {{-- ປຸ່ມພິມ --}}
                                    <div>
                                        @if($document->document_code)
                                            <a href="{{ route('staff.documents.print', $document->id) }}" target="_blank" class="inline-flex items-center text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 px-2.5 py-1.5 rounded-lg border border-gray-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                                ພີມ
                                            </a>
                                        @endif
                                    </div>

                                    {{-- ປຸ່ມດຳເນີນການ (Actions) --}}
                                    <div class="space-x-3">
                                        @if(in_array($document->status, ['DRAFT', 'REJECTED']))
                                            <a href="{{ route('staff.documents.edit', $document->id) }}" class="text-blue-600 font-semibold">
                                                ແກ້ໄຂ
                                            </a>
                                        @else
                                            <a href="{{ route('staff.documents.show', $document->id) }}" class="text-indigo-600 font-medium">
                                                ເບິ່ງລາຍລະອຽດ &rarr;
                                            </a>
                                        @endif

                                        @if($document->status === 'DRAFT')
                                            <form action="{{ route('staff.documents.submit', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານຕ້ອງການສົ່ງເອກະສານສະບັບນີ້ແມ່ນບໍ?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-green-600 font-semibold ml-1">ສົ່ງ</button>
                                            </form>

                                            <form action="{{ route('staff.documents.destroy', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານແນ່ໃນບໍວ່າຕ້ອງການລຶບເອກະສານສະບັບຮ່າງນີ້?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 ml-1">ລຶບ</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        @empty
                            <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed text-gray-500">
                                ທ່ານບໍ່ມີເອກະສານ
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