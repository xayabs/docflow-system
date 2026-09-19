<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">
            {{ __('ກວດສອບເອກະສານ:') }} {{ $document->title }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg rounded-xl border border-gray-200">
                <div class="p-4 sm:p-6 text-gray-900 space-y-6 md:space-y-8">
                    
                    {{-- ຂໍ້ຄວາມແຈ້ງເຕືອນ Error ຈາກ Validation --}}
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

                    {{-- ຂໍ້ຄວາມ/ໂນດສ່ວນຕົວເຖິງນາຍບັນຊີ --}}
                    @if(isset($privateNotes) && $privateNotes->isNotEmpty())
                        <div class="mb-4 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg shadow-sm">
                            <p class="font-bold text-amber-800 text-sm flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                                ຂໍ້ຄວາມ / ໂໜດສ່ວນຕົວເຖິງທ່ານ:
                            </p>
                            @foreach($privateNotes as $note)
                                <div class="mt-2 text-sm text-amber-900 bg-white p-3 rounded border border-amber-200">
                                    <p>{{ $note->note }}</p>
                                    <p class="text-xs text-right text-gray-500 mt-1">- ຈາກ: <span class="font-semibold">{{ $note->sender->name }}</span></p>
                                </div>
                            @endforeach
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
                                    <dd class="text-lg md:text-xl text-blue-600 font-bold">{{ number_format($document->total_amount, 2) }} KIP</dd>
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
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                        <h4 class="font-medium text-gray-900 text-sm mb-2">{{ $item->item_description }}</h4>
                                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 bg-gray-50 p-2 rounded">
                                            <div><span class="text-gray-400">ຈຳນວນ:</span> {{ number_format($item->quantity, 0) }}</div>
                                            <div class="text-right"><span class="text-gray-400">ລາຄາ:</span> {{ number_format($item->unit_price, 0) }}</div>
                                        </div>
                                        <div class="mt-2 text-right text-sm font-bold text-gray-900">
                                            ລວມ: <span class="text-blue-600">{{ number_format($item->total_price, 0) }}</span>
                                        </div>
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
                    {{-- Section 4: Action Buttons (Responsive & Alpine.js)          --}}
                    {{-- ========================================================== --}}
                    @if(in_array($document->status, ['PENDING_ACCOUNTANT_BUDGET_CHECK', 'PENDING_ACCOUNTANT_POSTING', 'PENDING_ACCOUNTANT_VERIFICATION']))
                        <div x-data="{ 
                            showNoteSection: false, 
                            showRejectReason: false,
                            notes: [], 
                            addNoteSet() { this.notes.push({ message: '', recipient_ids: [] }); } 
                        }"
                        class="mt-8 pt-6 border-t border-gray-200">

                            <h3 class="text-base md:text-lg font-bold mb-4 text-gray-800">ການດໍາເນີນການຄັດຕິດໂໜດ / ຄວາມເຫັນເພີ່ມເຕີມ</h3>
            
                            <div class="mb-4 bg-blue-50 p-3 rounded-lg border border-blue-100">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="showNoteSection" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-5 h-5">
                                    <span class="ml-3 text-sm font-medium text-blue-900">ຄັດຕິດໂໜດ / ຄວາມຄິດເຫັນເພີ່ມເຕີມ</span>
                                </label>
                            </div>

                            <form action="{{ route('accountant.documents.process', $document->id) }}" method="POST">
                                @csrf

                                {{-- ສ່ວນຂອງໂນດ --}}
                                <div x-show="showNoteSection" x-transition.opacity class="mb-6">
                                    <div class="space-y-4 border border-gray-200 p-4 md:p-5 rounded-xl bg-gray-50">
                                        <template x-for="(noteSet, index) in notes" :key="index">
                                            <div class="p-4 bg-white rounded-lg shadow-sm border border-gray-200 relative">
                                                <button type="button" @click="notes.splice(index, 1)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 hover:bg-red-50 p-1 rounded transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                </button>
                                
                                                <div class="mb-4">
                                                    <label class="block text-sm font-bold text-gray-700 mb-2">ສົ່ງເຖິງ (ເລືອກໄດ້ຫຼາຍຄົນ):</label>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 bg-gray-50 p-3 rounded border">
                                                        @if($recipientHeadFinance)
                                                            <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer border border-transparent hover:border-gray-200 transition">
                                                                <input type="checkbox" :name="'notes[' + index + '][recipient_ids][]'" value="{{ $recipientHeadFinance->id }}" x-model="noteSet.recipient_ids" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                                                <span class="ml-2 text-sm text-gray-700">ຫົວໜ້າພະແນກການເງິນ</span>
                                                            </label>
                                                        @endif

                                                        @if($recipientViceDean)
                                                            <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer border border-transparent hover:border-gray-200 transition">
                                                                <input type="checkbox" :name="'notes[' + index + '][recipient_ids][]'" value="{{ $recipientViceDean->id }}" x-model="noteSet.recipient_ids" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                                                <span class="ml-2 text-sm text-gray-700">ຮອງຄະນະບໍດີ</span>
                                                            </label>
                                                        @endif

                                                        @if($recipientDean)
                                                            <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer border border-transparent hover:border-gray-200 transition">
                                                                <input type="checkbox" :name="'notes[' + index + '][recipient_ids][]'" value="{{ $recipientDean->id }}" x-model="noteSet.recipient_ids" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                                                <span class="ml-2 text-sm text-gray-700">ຄະນະບໍດີ</span>
                                                            </label>
                                                        @endif
                                                    </div>
                                                </div>
                                            
                                                <div>
                                                    <label :for="'message_' + index" class="block text-sm font-bold text-gray-700 mb-1">ຂໍ້ຄວາມ:</label>
                                                    <textarea :name="'notes[' + index + '][message]'" x-model="noteSet.message" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="ພິມຂໍ້ຄວາມທີ່ນີ້..."></textarea>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <button type="button" @click="addNoteSet()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition w-full sm:w-auto justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                            ເພີ່ມຂໍ້ຄວາມ / ກຸ່ມຜູ້ຮັບອື່ນ
                                        </button>
                                    </div>
                                </div>
                
                                <template x-for="(noteSet, index) in notes">
                                    <div>
                                        <input type="hidden" :name="'notes[' + index + '][message]'" :value="noteSet.message">
                                        <template x-for="recipientId in noteSet.recipient_ids">
                                            <input type="hidden" :name="'notes[' + index + '][recipient_ids][]'" :value="recipientId">
                                        </template>
                                    </div>
                                </template>

                                {{-- Textarea ປະຕິເສດ --}}
                                <div x-show="showRejectReason" x-transition.scale.origin.top class="mb-6 p-4 border border-red-200 bg-red-50 rounded-lg shadow-sm">
                                    <x-input-label for="rejection_reason" value="ເຫດຜົນໃນການປະຕິເສດ (ຕ້ອງລະບຸ)" class="text-red-800 font-bold" />
                                    <textarea id="rejection_reason" name="rejection_reason" rows="3" class="block mt-2 w-full border-red-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="ກະລຸນາພິມເຫດຜົນ..."></textarea>
                                </div>
                                    
                                {{-- ປຸ່ມດຳເນີນການລຸ່ມສຸດ (Responsive) --}}
                                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end mt-8 gap-3 sm:gap-4 border-t pt-4">
                                    <a href="{{ route('accountant.dashboard') }}" class="w-full sm:w-auto text-center px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 transition">
                                        ກັບຄືນ
                                    </a>
                                        
                                    <button type="button" @click="showRejectReason = true" x-show="!showRejectReason" class="w-full sm:w-auto justify-center inline-flex items-center px-4 py-2.5 bg-white border-2 border-red-500 text-red-600 rounded-lg font-semibold text-sm hover:bg-red-50 transition">
                                        ສົ່ງເອກະສານກັບ
                                    </button>
                        
                                    <button type="submit" name="action" value="approve" x-show="!showRejectReason" class="w-full sm:w-auto justify-center inline-flex items-center px-6 py-2.5 bg-blue-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-blue-700 transition shadow-md" onclick="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການອະນຸມັດ ແລະ ສົ່ງຕໍ່ເອກະສານນີ້?')">
                                        ອະນຸມັດ (ສົ່ງຕໍ່)
                                    </button>

                                    <button type="submit" name="action" value="reject" x-show="showRejectReason" class="w-full sm:w-auto justify-center inline-flex items-center px-6 py-2.5 bg-red-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-red-700 transition shadow-md" onclick="return confirm('ທ່ານຢືນຢັນທີ່ຈະສົ່ງເອກະສານນີ້ກັບແມ່ນບໍ?')">
                                        ຢືນຢັນການສົ່ງກັບ
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        {{-- ປຸ່ມກັບຄືນຢ່າງດຽວ (Responsive) --}}
                        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end">
                            <a href="{{ url()->previous() }}" class="w-full sm:w-auto text-center inline-flex items-center px-6 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                &larr; ກັບຄືນ
                            </a>
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>