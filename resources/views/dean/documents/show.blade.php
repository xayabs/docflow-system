<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ກວດສອບເອກະສານ:') }} {{ $document->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <strong class="font-bold">ເກີດຂໍ້ຜິດພາດ!</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(isset($privateNotes) && $privateNotes->isNotEmpty())
                        <div class="mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                            <p class="font-bold">ຂໍ້ຄວາມ/ໂໜດສ່ວນຕົວເຖິງທ່ານ:</p>
                            @foreach($privateNotes as $note)
                                <div class="mt-2">
                                    <p>{{ $note->note }}</p>
                                    <p class="text-xs text-right">- ຈາກ: {{ $note->sender->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    {{-- Section 1: Document Summary (ຄືເກົ່າ) --}}
                    <div>
                        <h3 class="text-lg font-medium border-b pb-2 mb-4">ສະຫຼຸບຂໍ້ມູນເອກະສານ</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <dt class="text-base font-medium text-gray-500">ສະຖານະ</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full {{ getStatusColorClass($document->status) }}">
                                {{ translateStatus($document->status) }}</dd>
                                <!--inline-block">{{ $document->status }}-->
                            </div>
                            <div>
                                <dt class="text-base font-medium text-gray-500">ປະເພດ</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $document->documentType->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-base font-medium text-gray-500">ຜູ້ຮ້ອງຂໍ</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $document->requester->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-base font-medium text-gray-500">ພາກສ່ວນ</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $document->requester->department->name ?? 'N/A' }}</dd>
                            </div>
                            @if($document->document_type_id != 2)
                            <div>
                                <dt class="text-base font-medium text-gray-500">ມູນຄ່າລວມ</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold">{{ number_format($document->total_amount, 2) }} KIP</dd>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Section 2: Document Items (ຄືເກົ່າ) --}}
                    @if($document->document_type_id != 2)
                    <div>
                        <h3 class="text-lg font-medium border-b pb-2 mb-4">ລາຍການເບີກຈ່າຍ</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-base font-bold text-gray-500 uppercase">ລາຍລະອຽດ</th>
                                    <th class="px-6 py-3 text-right text-base font-bold text-gray-500 uppercase">ຈຳນວນ</th>
                                    <th class="px-6 py-3 text-right text-base font-bold text-gray-500 uppercase">ລາຄາຕໍ່ໜ່ວຍ</th>
                                    <th class="px-6 py-3 text-right text-base font-bold text-gray-500 uppercase">ລາຄາລວມ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($document->documentItems as $item)
                                <tr>
                                    <td class="px-6 py-4">{{ $item->item_description }}</td>
                                    <td class="px-6 py-4 text-right">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4 text-right">{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        ບໍ່ມີລາຍການເບີກຈ່າຍ
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                            {{-- แสดงยอดรวมก็ต่อเมื่อมีรายการ --}}
                            @if($document->documentItems->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right font-bold">ລວມທັງໝົດ:</td>
                                        <td class="px-6 py-4 text-right font-bold">{{ number_format($document->total_amount, 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                    @endif

                    {{-- Section 3: Attachments (ຄືເກົ່າ) --}}
                    @if($document->document_type_id != 2)
                    <div>
                        <h3 class="text-lg font-medium border-b pb-2 mb-4">ໄຟລ໌ແນບ</h3>
                        <ul class="list-disc pl-5">
                            @forelse ($document->attachments as $attachment)
                                <li>
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="text-blue-600 hover:underline">
                                        {{ $attachment->file_name }}
                                    </a>
                                </li>
                            @empty
                                <li>ບໍ່ມີໄຟລ໌ແນບ</li>
                            @endforelse
                        </ul>
                    </div>
                    @endif
                    
                    {{-- Section: Document History --}}
                    <x-document-history :logs="$document->documentLogs" />
                        
                    {{-- ====================================================== --}}
                    {{-- ===== Section 4: Action Buttons (ສ່ວນທີ່ເພີ່ມໃໝ່) ===== --}}
                    {{-- ====================================================== --}}
             
                    @if(in_array($document->status, ['PENDING_DEAN_FINAL_APPROVAL', 'PENDING_DEAN_APPROVAL']))

                    <div class="mt-6 pt-4 border-t">
                        <h3 class="text-lg font-medium mb-4">ການດຳເນີນການ</h3>
        
                        {{-- ============================================= --}}
                        {{-- ===== เริ่มส่วนที่แก้ไข Layout ===== --}}
                        {{-- ============================================= --}}
        
                        {{-- เราจะมีฟอร์มเดียวที่ครอบทุกอย่าง --}}
                        <form action="" method="POST" id="deanActionForm">
                            @csrf

                            {{-- Textarea สำหรับเหตุผล (จะถูกเปิด/ปิดด้วย JS) --}}
                            <div id="rejectionReasonContainer" class="mb-4" style="display: none;">
                                <x-input-label for="rejection_reason" value="ເຫດຜົນໃນການສົ່ງກັບ (ຕ້ອງລະບຸ)" />
                                <textarea id="rejection_reason" name="rejection_reason" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                            </div>

                            {{-- Div ครอบปุ่มทั้งหมด --}}
                            <div class="flex justify-end items-center space-x-4">
                
                                {{-- ปุ่มกลับคืน route('dean.dashboard') --}}
                                <a href="{{ route('dean.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                    ກັບຄືນ
                                </a>
                
                                {{-- ปุ่มปฏิเสธ (ใช้ JS) --}}
                                <x-danger-button type="button" onclick="prepareReject()">
                                    ສົ່ງເອກະສານກັບ
                                </x-danger-button>
                
                                {{-- ปุ่มอนุมัติ (ใช้ JS) --}}
                                <x-primary-button type="button" onclick="prepareApprove()">
                                    ອະນຸມັດ (ສົ່ງຕໍ່)
                                </x-primary-button>
                
                            </div>
                        </form>

                        {{-- ============================================== --}}
                        {{-- ===== JavaScript สำหรับควบคุมฟอร์ม ===== --}}
                        {{-- ============================================== --}}
                        <script>
                            const deanForm = document.getElementById('deanActionForm');
                            const reasonContainer = document.getElementById('rejectionReasonContainer');
                            const reasonInput = document.getElementById('rejection_reason');

                            function prepareApprove() {
                            if (confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການອະນຸມັດແລະສົ່ງຕໍ່ເອກະສານນີ້?')) {
                                deanForm.action = "{{ route('dean.documents.approve', $document->id) }}";
                                    deanForm.submit();
                                }
                            }

                            function prepareReject() {
                                // แสดงช่องเหตุผล
                                reasonContainer.style.display = 'block';
                                reasonInput.setAttribute('required', 'required');
                
                                // เปลี่ยนฟังก์ชันของปุ่มอนุมัติ ให้กลายเป็นปุ่มยืนยันการปฏิเสธ
                                const approveBtn = deanForm.querySelector('button[onclick="prepareApprove()"]');
                                approveBtn.innerText = 'ຢືນຢັນການສົ່ງກັບ';
                                approveBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700'); // ลบสีน้ำเงิน
                                approveBtn.classList.add('bg-red-600', 'hover:bg-red-500');   // เพิ่มสีแดง
                
                                approveBtn.onclick = function() {
                                if (reasonInput.value.length >= 10) {
                                    if (confirm('ທ່ານຢືນຢັນທີ່ຈະສົ່ງກັບເອກະສານນີ້ແມ່ນບໍ?')) {
                                        deanForm.action = "{{ route('dean.documents.reject', $document->id) }}";
                                            deanForm.submit();
                                        }
                                    } else {
                                        alert('ກະລຸນາປ້ອນເຫດຜົນໃນການສົ່ງກັບ ຢ່າງໜ້ອຍ 10 ຕົວອັກສອນ');
                                        reasonInput.focus();
                                    }
                                };
                
                                // ซ่อนปุ่มปฏิเสธเดิม
                                const rejectBtn = deanForm.querySelector('button[onclick="prepareReject()"]');
                                rejectBtn.style.display = 'none';
                            }
                        </script>
        
                    </div>
                    @else
                        {{-- ปุ่ม "กลับคืน" อย่างเดียว --}}
                        <div class="mt-6 text-right">
                            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                ກັບຄືນ
                            </a>
                        </div>
                    @endif

                    <!--@if(in_array($document->status, ['PENDING_DEAN_FINAL_APPROVAL', 'PENDING_DEAN_APPROVAL']))
                    <div class="mt-6 pt-4 border-t">
                        <h3 class="text-lg font-medium mb-4">ການດຳເນີນການ</h3>
    
                        {{-- Reject Form --}}
                        <form action="{{ route('dean.documents.reject', $document->id) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <x-input-label for="rejection_reason" value="ເຫດຜົນໃນການສົ່ງກັບ (ຕ້ອງລະບຸ)" />
                                <textarea id="rejection_reason" name="rejection_reason" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                            </div>
        
                            {{-- ປຸ່ມ Reject ຈະ submit ຟອມນີ້ --}}
                            <x-danger-button type="submit" onclick="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການສົ່ງເອກະສານນີ້ກັບ?')">
                                ສົ່ງເອກະສານກັບ
                            </x-danger-button>
                        </form>

                        {{-- Approve Form - ວາງແຍກອອກມາຕ່າງຫາກເພື່ອຄວາມຊັດເຈນ --}}
                        <div class="mt-4 text-right"> {{-- ໃຊ້ text-right ເພື່ອຍູ້ປຸ່ມໄປທາງຂວາ --}}
                            <form action="{{ route('dean.documents.approve', $document->id) }}" method="POST" class="inline-block">
                                @csrf
                                <x-primary-button type="submit" onclick="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການອະນຸມັດ ແລະ ສົ່ງຕໍ່ເອກະສານນີ້?')">
                                    ອະນຸມັດ (ສົ່ງຕໍ່)
                                </x-primary-button>
                            </form>
                        </div>
                        @endif
                        {{-- Back Button - ວາງໄວ້ລຸ່ມສຸດກໍໄດ້ ຫຼື ບ່ອນທີ່ເໝາະສົມ --}}
                        <div class="mt-6 text-right">
                            <a href="{{ route('dean.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                ກັບຄືນ
                            </a>
                        </div>-->
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

