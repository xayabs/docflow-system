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

                    {{-- Section 1: Document Summary --}}
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <h3 class="text-base md:text-lg font-bold border-b border-gray-200 pb-2 mb-4 text-gray-800">ສະຫຼຸບຂໍ້ມູນເອກະສານ</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ສະຖານະ</dt>
                                <dd class="text-sm">
                                    <span class="px-2.5 py-1 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                        {{ translateStatus($document->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ປະເພດ</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ $document->documentType->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ຜູ້ຮ້ອງຂໍ</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ $document->requester->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ມູນຄ່າລວມ</dt>
                                <dd class="text-lg text-blue-600 font-bold">{{ number_format($document->total_amount, 0) }} KIP</dd>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Document Items --}}
                    @if($document->document_type_id != 2)
                    <div>
                        <h3 class="text-base md:text-lg font-bold border-b border-gray-200 pb-2 mb-4 text-gray-800">ລາຍການເບີກຈ່າຍ</h3>
                        <div class="hidden md:block overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">ລາຍລະອຽດ</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">ຈຳນວນ</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">ລາຄາຕໍ່ໜ່ວຍ</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">ລາຄາລວມ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($document->documentItems as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm">{{ $item->item_description }}</td>
                                            <td class="px-6 py-4 text-right text-sm">{{ number_format($item->quantity, 0) }}</td>
                                            <td class="px-6 py-4 text-right text-sm">{{ number_format($item->unit_price, 0) }}</td>
                                            <td class="px-6 py-4 text-right text-sm font-bold text-blue-600">{{ number_format($item->total_price, 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Mobile View --}}
                        <div class="block md:hidden space-y-3">
                            @foreach ($document->documentItems as $item)
                                <div class="bg-white border p-3 rounded-lg text-sm">
                                    <p class="font-bold">{{ $item->item_description }}</p>
                                    <p class="text-xs text-gray-500">ຈຳນວນ: {{ number_format($item->quantity, 0) }} | ລາຄາ: {{ number_format($item->unit_price, 0) }}</p>
                                    <p class="text-right font-bold text-blue-600">{{ number_format($item->total_price, 0) }} ກີບ</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Section: Document History --}}
                    <x-document-history :logs="$document->documentLogs" />
                        
                    {{-- Section 4: Action Buttons (Responsive) --}}
                    @if(in_array($document->status, ['PENDING_DEAN_FINAL_APPROVAL', 'PENDING_DEAN_APPROVAL']))
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-base md:text-lg font-bold mb-4 text-gray-800">ການດຳເນີນການ</h3>
                            <form action="" method="POST" id="deanActionForm">
                                @csrf
                                <div id="rejectionReasonContainer" class="mb-4 bg-red-50 p-4 rounded-lg border border-red-200" style="display: none;">
                                    <x-input-label for="rejection_reason" value="ເຫດຜົນໃນການສົ່ງເອກະສານກັບ (ຕ້ອງລະບຸ)" class="text-red-800" />
                                    <textarea id="rejection_reason" name="rejection_reason" rows="3" class="w-full mt-2 rounded-lg border-red-300"></textarea>
                                </div>
                                <div class="flex flex-col-reverse sm:flex-row gap-3 justify-end">
                                    <a href="{{ route('dean.dashboard') }}" class="w-full sm:w-auto text-center px-4 py-2.5 bg-gray-100 rounded-lg font-semibold text-sm">ກັບຄືນ</a>
                                    <button type="button" onclick="prepareReject()" class="w-full sm:w-auto px-4 py-2.5 bg-white border-2 border-red-500 text-red-600 rounded-lg font-semibold text-sm">ສົ່ງເອກະສານກັບ</button>
                                    <button type="button" onclick="prepareApprove()" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg font-bold text-sm">ອະນຸມັດ (ສົ່ງຕໍ່)</button>
                                </div>
                            </form>
                            <script>
                                const deanForm = document.getElementById('deanActionForm');
                                const reasonContainer = document.getElementById('rejectionReasonContainer');
                                const reasonInput = document.getElementById('rejection_reason');
                                function prepareApprove() {
                                    if (confirm('ທ່ານແນ່ໃຈບໍ່?')) {
                                        deanForm.action = "{{ route('dean.documents.approve', $document->id) }}";
                                        deanForm.submit();
                                    }
                                }
                                function prepareReject() {
                                    reasonContainer.style.display = 'block';
                                    reasonInput.setAttribute('required', 'required');
                                    const approveBtn = deanForm.querySelector('button[onclick="prepareApprove()"]');
                                    approveBtn.style.display = 'none';
                                    const rejectBtn = deanForm.querySelector('button[onclick="prepareReject()"]');
                                    rejectBtn.style.display = 'none';
                                    
                                    // เพิ่มปุ่มยืนยันการปฏิเสธ
                                    const confirmReject = document.createElement('button');
                                    confirmReject.type = 'submit';
                                    confirmReject.name = 'action';
                                    confirmReject.value = 'reject';
                                    confirmReject.className = 'w-full sm:w-auto px-6 py-2.5 bg-red-600 text-white rounded-lg font-bold text-sm';
                                    confirmReject.innerText = 'ຢືນຢັນການສົ່ງເອກະສານກັບ';
                                    deanForm.querySelector('.flex').appendChild(confirmReject);
                                }
                            </script>
                        </div>
                    @else
                        <div class="mt-8 pt-6 border-t text-right">
                            <a href="{{ url()->previous() }}" class="inline-flex px-6 py-2.5 bg-gray-100 rounded-lg font-semibold text-sm">ກັບຄືນ</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>