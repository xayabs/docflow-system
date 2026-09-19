<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ເອກະສານຂອງ ເລຂາຄະນະບໍດີ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
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
                    
                    <h3 class="text-lg font-medium mb-4">ລາຍການເອກະສານທີ່ລໍຖ້າການກວດສອບ</h3>
                    
                    {{-- ຟອມຄົ້ນຫາ ແລະ ກັ່ນຕອງ --}}
                    <x-document-filter-form 
                        :action="route('secretary.dashboard')" 
                        :departments="$departments"
                        title-span="md:col-span-3" 
                    />
                                        
                    {{-- ========================================================================= --}}
                    {{-- 1. ສ່ວນສະແດງຜົນເທິງຈໍຄອມພິວເຕີ (Desktop Table: hidden md:block) --}}
                    {{-- ========================================================================= --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ລະຫັດເອກະສານ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຫົວຂໍ້ເອກະສານ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ປະເພດ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ພາກສ່ວນສະເໜີ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ວັນທີສົ່ງ</th>
                                    <th class="relative px-6 py-3 text-center text-base font-bold">ການດໍາເນີນການ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($pendingDocuments as $document)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 text-center font-mono text-sm">{{ $document->document_code ?? '-' }}</td>
                                        <td class="px-6 py-4 font-medium">{{ $document->title }}</td>
                                        <td class="px-6 py-4 text-center">{{ $document->documentType->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-center">{{ $document->requester->department->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-center text-xs text-gray-500">{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('secretary.documents.show', $document->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                                                ກວດສອບ ແລະ ດຳເນີນການ &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            ບໍ່ມີເອກະສານທີ່ລໍຖ້າການກວດສອບ
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
                        @forelse ($pendingDocuments as $document)
                            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm space-y-3 relative">
                                
                                {{-- ແຖບສີບອກສະຖານະດ່ວນ (ສຳລັບເລຂາ, ເອກະສານເຂົ້າໃໝ່ອາດຈະໃຊ້ສີຟ້າອ່ອນ) --}}
                                <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-400 rounded-l-xl"></div>

                                {{-- ແຖວເທິງສຸດ: ລະຫັດເອກະສານ ແລະ ວັນທີ --}}
                                <div class="flex justify-between items-center pl-2">
                                    <span class="font-mono text-xs font-bold text-gray-700 bg-gray-100 px-2 py-1 rounded border">
                                        {{ $document->document_code ?? '-' }}
                                    </span>
                                    <span class="text-xs text-gray-500 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ $document->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                {{-- ສ່ວນກາງ: ຫົວຂໍ້ເອກະສານ ແລະ ລາຍລະອຽດຍ່ອຍ --}}
                                <div class="pl-2">
                                    <h4 class="font-bold text-gray-900 text-base leading-snug">{{ $document->title }}</h4>
                                    <div class="mt-2 text-sm text-gray-600 space-y-1 bg-gray-50 p-2 rounded-lg border border-gray-100">
                                        <p class="flex items-start">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 mt-0.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            <span class="font-medium mr-1">ປະເພດ:</span> {{ $document->documentType->name ?? 'N/A' }}
                                        </p>
                                        <p class="flex items-start">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 mt-0.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                            <span class="font-medium mr-1">ພາກສ່ວນ:</span> <span class="truncate">{{ $document->requester->department->name ?? 'N/A' }}</span>
                                        </p>
                                    </div>
                                </div>

                                {{-- ແຖວລຸ່ມສຸດ: ປຸ່ມດຳເນີນການ --}}
                                <div class="pt-2 pl-2 flex justify-end">
                                    <a href="{{ route('secretary.documents.show', $document->id) }}" class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2.5 bg-indigo-600 text-white rounded-lg font-semibold text-sm hover:bg-indigo-700 shadow-sm transition">
                                        ກວດສອບ ແລະ ດຳເນີນການ
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </a>
                                </div>

                            </div>
                        @empty
                            <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <p>ບໍ່ມີເອກະສານທີ່ລໍຖ້າການກວດສອບ</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-6">
                        {{ $pendingDocuments->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>