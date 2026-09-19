<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">
            {{ __('ປະເມີນເອກະສານ:') }} {{ $document->title }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg rounded-xl border border-gray-200">
                <div class="p-4 sm:p-6 text-gray-900 space-y-6 md:space-y-8">
                    
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
                            <strong class="font-bold">ເກີດຂໍ້ຜິດພາດ!</strong>
                            <ul class="mt-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- Section 1: Document Summary (Responsive)                    --}}
                    {{-- ========================================================== --}}
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <h3 class="text-base md:text-lg font-bold border-b border-gray-200 pb-2 mb-4 text-gray-800">ສະຫຼຸບຂໍ້ມູນເອກະສານ</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ສະຖານະ</dt>
                                <dd class="text-sm">
                                    <span class="px-2.5 py-1 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                        {{ translateStatus($document->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ປະເພດ</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ $document->documentType->name ?? 'N/A' }}</dd>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ຜູ້ຮ້ອງຂໍ</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ $document->requester->name ?? 'N/A' }}</dd>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ພາກສ່ວນ</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ $document->requester->department->name ?? 'N/A' }}</dd>
                            </div>
                            @if($document->document_type_id != 2)
                                <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 sm:col-span-2 lg:col-span-4 lg:bg-transparent lg:shadow-none lg:border-0 lg:p-0">
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ມູນຄ່າລວມທັງໝົດ</dt>
                                    <dd class="text-lg md:text-xl text-blue-600 font-bold">{{ number_format($document->total_amount, 0) }} KIP</dd>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ========================================================== --}}
                    {{-- Section 2: Document Items (Responsive Table/Cards)          --}}
                    {{-- ========================================================== --}}
                    @if($document->document_type_id != 2)
                        <div>
                            <h3 class="text-base md:text-lg font-bold border-b border-gray-200 pb-2 mb-4 text-gray-800">ລາຍການເບີກຈ່າຍ</h3>
                            
                            {{-- 2.1 ສຳລັບຈໍຄອມພິວເຕີ (Desktop Table) --}}
                            <div class="hidden md:block overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ລາຍລະອຽດ</th>
                                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">ຈຳນວນ</th>
                                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">ລາຄາຕໍ່ໜ່ວຍ</th>
                                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">ລາຄາລວມ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($document->documentItems as $item)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->item_description }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($item->quantity, 0) }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($item->unit_price, 0) }}</td>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">{{ number_format($item->total_price, 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">ບໍ່ມີລາຍການເບີກຈ່າຍ</td></tr>
                                        @endforelse
                                    </tbody>
                                    @if($document->documentItems->isNotEmpty())
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="3" class="px-6 py-4 text-right text-sm font-bold text-gray-900">ລວມທັງໝົດ:</td>
                                                <td class="px-6 py-4 text-right text-sm font-bold text-blue-600">{{ number_format($document->total_amount, 0) }}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>

                            {{-- 2.2 ສຳລັບຈໍມືຖື (Mobile Cards) --}}
                            <div class="block md:hidden space-y-3">
                                @forelse ($document->documentItems as $item)
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-sm">
                                        <h4 class="font-medium text-gray-900 mb-2">{{ $item->item_description }}</h4>
                                        <div class="flex justify-between text-gray-600">
                                            <span>ຈຳນວນ: {{ number_format($item->quantity, 0) }}</span>
                                            <span>ລາຄາ: {{ number_format($item->unit_price, 0) }}</span>
                                        </div>
                                        <div class="mt-2 text-right font-bold text-blue-600">ລວມ: {{ number_format($item->total_price, 0) }}</div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 bg-gray-50 rounded-lg border text-sm text-gray-500">ບໍ່ມີລາຍການເບີກຈ່າຍ</div>
                                @endforelse
                                @if($document->documentItems->isNotEmpty())
                                    <div class="text-right font-bold text-lg text-gray-900 mt-4 border-t pt-2">
                                        ລວມທັງໝົດ: <span class="text-blue-600">{{ number_format($document->total_amount, 0) }} ກີບ</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- Section 3: Attachments                                      --}}
                    {{-- ========================================================== --}}
                    @if($document->document_type_id != 2)
                        <div>
                            <h3 class="text-base md:text-lg font-bold border-b border-gray-200 pb-2 mb-4 text-gray-800">ໄຟລ໌ແນບ</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                @forelse ($document->attachments as $attachment)
                                    <li>
                                        <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center text-sm md:text-base">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                            <span class="truncate max-w-[200px] sm:max-w-xs">{{ $attachment->file_name }}</span>
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-sm text-gray-500">ບໍ່ມີໄຟລ໌ແນບ</li>
                                @endforelse
                            </ul>
                        </div>
                    @endif
                    
                    {{-- Section: Document History --}}
                    <x-document-history :logs="$document->documentLogs" />
                        
                    {{-- ========================================================== --}}
                    {{-- Section 4: Action Buttons (Responsive)                      --}}
                    {{-- ========================================================== --}}
                    
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-base md:text-lg font-bold mb-4 text-gray-800">ການດຳເນີນການ (ຝ່າຍຈັດຊື້)</h3>
                        
                        {{-- ໃຊ້ flex-col-reverse ໃນມືຖືເພື່ອໃຫ້ປຸ່ມກັບຄືນຢູ່ລຸ່ມສຸດ, ແລະ row ໃນຈໍໃຫຍ່ --}}
                        <div class="flex flex-col-reverse sm:flex-row gap-3 sm:items-center sm:justify-end">
                            
                            {{-- ປຸ່ມກັບຄືນ --}}
                            <a href="{{ route('procurement.dashboard') }}" class="w-full sm:w-auto text-center px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 transition">
                                ກັບຄືນ
                            </a>
                            
                            {{-- ປຸ່ມພິມ: ສະແດງສະເພາະເອກະສານຂໍຖອນເງິນ --}}
                            @if($document->document_type_id == 1)
                                <a href="{{ route('procurement.documents.print', $document->id) }}" target="_blank" class="w-full sm:w-auto justify-center inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                    ພີມເອກະສານ
                                </a>
                            @endif

                            {{-- ===== Logic ການສະແດງປຸ່ມຕາມສະຖານະ ===== --}}
                            @if($document->status === 'PENDING_PROCUREMENT_EVALUATION')
                                <form action="{{ route('procurement.documents.startProcess', $document->id) }}" method="POST" class="w-full sm:w-auto" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການເລີ່ມດຳເນີນການຈັດຊື້ສຳລັບເອກະສານນີ້?')">
                                    @csrf
                                    <button type="submit" class="w-full justify-center inline-flex items-center px-6 py-2.5 bg-blue-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-blue-700 transition shadow-md">
                                        ເລີ່ມດຳເນີນການຈັດຊື້
                                    </button>
                                </form>

                            @elseif($document->status === 'PROCUREMENT_IN_PROGRESS')
                                <form action="{{ route('procurement.documents.completePurchase', $document->id) }}" method="POST" class="w-full sm:w-auto" onsubmit="return confirm('ທ່ານຢືນຢັນວ່າການຈັດຊື້/ສ້ອມແປງສຳເລັດແລ້ວແມ່ນບໍ່?')">
                                    @csrf
                                    <button type="submit" class="w-full justify-center inline-flex items-center px-6 py-2.5 bg-blue-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-blue-700 transition shadow-md">
                                        ຢືນຢັນການຈັດຊື້ສຳເລັດ
                                    </button>
                                </form>

                            @elseif($document->status === 'PURCHASE_COMPLETE_PENDING_PAYMENT')
                                <a href="{{ route('procurement.documents.createPaymentRequest.form', $document->id) }}" class="w-full sm:w-auto justify-center inline-flex items-center px-6 py-2.5 bg-green-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-green-700 transition shadow-md">
                                    ສ້າງເອກະສານຂໍຖອນເງິນ
                                </a>
                            @endif

                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>