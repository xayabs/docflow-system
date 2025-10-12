<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ກວດສອບເອກະສານ:') }} {{ $document->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                            <div>
                                <dt class="text-base font-medium text-gray-500">ມູນຄ່າລວມ</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold">{{ number_format($document->total_amount, 2) }} KIP</dd>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Document Items (ຄືເກົ່າ) --}}
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

                    {{-- Section 3: Attachments (ຄືເກົ່າ) --}}
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
                    
                    {{-- Section: Document History --}}
                    <x-document-history :logs="$document->documentLogs" />
                        
                {{-- ========================================================== --}}
                {{-- ===== Section 4: Action Buttons (ฉบับ Alpine.js ขั้นสูง) ===== --}}
                {{-- ========================================================== --}}
                    @if($document->status === 'PENDING_FINANCE_PREPARER_REVIEW')

                        {{-- 1. Alpine.js Component หลัก ครอบทุกอย่าง --}}
                        <div x-data="{ 
                            showNoteSection: false, 
                            showRejectReason: false,
                            notes: [], 
                            addNoteSet() { this.notes.push({ message: '', recipient_ids: [] }); } 
                        }"
                        class="mt-6 pt-4 border-t">

                        <h3 class="text-lg font-medium mb-4">ການດໍາເນີນການຄັດຕິດໂໜດ/ຄວາມເຫັນເພີ່ມເຕີມ</h3>
        
                        {{-- 2. Checkbox สำหรับเปิด/ปิดส่วนของโน้ต --}}
                        <div class="mb-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" x-model="showNoteSection" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2">ຄັດຕິດໂໜດ/ຄວາມຄິດເຫັນເພີ່ມເຕີມ</span>
                            </label>
                        </div>

                        {{-- 3. ฟอร์มเดียวสำหรับทุกอย่าง --}}
                        <form action="{{ route('finance.preparer.documents.process', $document->id) }}" method="POST">
                            @csrf

                            {{-- 4. ส่วนของโน้ต (จะแสดงเมื่อ showNoteSection เป็น true) --}}
                            <div x-show="showNoteSection" x-transition>
                                <div class="mt-2 space-y-4 border p-4 rounded-md mb-4">
                                    <template x-for="(noteSet, index) in notes" :key="index">
                                        <div class="p-3 bg-gray-50 rounded-md border relative">
                                            <button type="button" @click="notes.splice(index, 1)" class="absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold">&times;</button>
                            
                                            {{-- Checkboxes แบบ Static --}}
                                            <div class="mb-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ສົ່ງເຖິງ (ເລືອກໄດ້ຫຼາຍຄົນ):</label>

                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                                {{-- ===== สร้าง Checkbox แบบ Manual ที่นี่ ===== --}}
                                                {{-- Checkbox 1: นายบัญชี --}}
                                                @if($recipientAccountant)
                                                    <label class="flex items-center">
                                                        <input type="checkbox" :name="'notes[' + index + '][recipient_ids][]'" value="{{ $recipientAccountant->id }}" x-model="noteSet.recipient_ids" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                        <span class="ml-2 text-sm">ນາຍບັນຊີ</span>
                                                    </label>
                                                @endif

                                                {{-- Checkbox 2: หัวหน้าการเงิน --}}
                                                @if($recipientHeadFinance)
                                                    <label class="flex items-center">
                                                        <input type="checkbox" :name="'notes[' + index + '][recipient_ids][]'" value="{{ $recipientHeadFinance->id }}" x-model="noteSet.recipient_ids" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                        <span class="ml-2 text-sm">ຫົວໜ້າພະແນກການເງິນ</span>
                                                    </label>
                                                @endif

                                                {{-- Checkbox 3: รองคณบดี --}}
                                                @if($recipientViceDean)
                                                    <label class="flex items-center">
                                                        <input type="checkbox" :name="'notes[' + index + '][recipient_ids][]'" value="{{ $recipientViceDean->id }}" x-model="noteSet.recipient_ids" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                        <span class="ml-2 text-sm">ຮອງຄະນະບໍດີ</span>
                                                    </label>
                                                @endif

                                                {{-- Checkbox 4: คณบดี --}}
                                                @if($recipientDean)
                                                    <label class="flex items-center">
                                                        <input type="checkbox" :name="'notes[' + index + '][recipient_ids][]'" value="{{ $recipientDean->id }}" x-model="noteSet.recipient_ids" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                        <span class="ml-2 text-sm">ຄະນະບໍດີ</span>
                                                    </label>
                                                @endif
                                                </div>
                                            </div>
                                        
                                            {{-- Textarea สำหรับข้อความของชุดโน้ตนี้ --}}
                                            <div>
                                                <label :for="'message_' + index" class="block text-sm font-medium text-gray-700">ຂໍ້ຄວາມ:</label>
                                                <textarea :name="'notes[' + index + '][message]'" x-model="noteSet.message" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                                            </div>
                                        </div>
                                    </template>
                                    {{-- ปุ่มเพิ่มชุดโน้ตใหม่ --}}
                                    <button type="button" @click="addNoteSet()" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                        + ເພີ່ມຂໍ້ຄວາມ/ກຸ່ມຜູ້ຮັບອື່ນ
                                    </button>
                                </div>
                            </div>
            
                            {{-- ส่งข้อมูล notes ไปกับฟอร์มหลัก --}}
                            <template x-for="(noteSet, index) in notes">
                                <div>
                                    <input type="hidden" :name="'notes[' + index + '][message]'" :value="noteSet.message">
                                    <template x-for="recipientId in noteSet.recipient_ids">
                                        <input type="hidden" :name="'notes[' + index + '][recipient_ids][]'" :value="recipientId">
                                    </template>
                                </div>
                            </template>

                            {{-- 5. Textarea สำหรับเหตุผลการปฏิเสธ (อยู่ข้างในฟอร์ม) --}}
                            <div x-show="showRejectReason" x-transition class="mb-4">
                                <x-input-label for="rejection_reason" value="ເຫດຜົນໃນການປະຕິເສດ (ຕ້ອງລະບຸ)" />
                                <textarea id="rejection_reason" name="rejection_reason" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                            </div>
                                
                            {{-- 6. ปุ่ม Actions (อยู่ข้างในฟอร์ม) ที่ควบคุมด้วย Alpine.js--}}
                            <div class="flex justify-end items-center space-x-4">
                                <a href="{{ route('finance.preparer.dashboard') }}" class="btn-secondary inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">ກັບຄືນ</a>
                                    
                                {{-- ปุ่ม "ปฏิเสธ" (จะแสดงเมื่อยังไม่กด) --}}
                                <x-danger-button type="button" @click="showRejectReason = true" x-show="!showRejectReason" class="btn-danger">
                                    ສົ່ງເອກະສານກັບ
                                </x-danger-button>
                    
                                {{-- ปุ่ม "อนุมัติ" (จะแสดงเมื่อยังไม่กดปฏิเสธ) --}}
                                <x-primary-button type="submit" name="action" value="approve" x-show="!showRejectReason" class="btn-primary" onclick="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການອະນຸມັດ ແລະ ສົ່ງຕໍ່ເອກະສານນີ້?')">
                                    ອະນຸມັດ (ສົ່ງຕໍ່)
                                </x-primary-button>

                                {{-- ปุ่ม "ยืนยันการปฏิเสธ" (จะแสดงเมื่อกดปฏิเสธแล้ว) --}}
                                <x-danger-button type="submit" name="action" value="reject" x-show="showRejectReason" class="btn-danger" onclick="return confirm('ທ່ານຢືນຢັນທີ່ຈະສົ່ງເອກະສານນີ້ກັບແມ່ນບໍ?')">
                                    ຢືນຢັນການສົ່ງເອກະສານກັບ
                                </x-danger-button>
                            </div>
                        </form>
                    </div>
                    @else
                        {{-- ถ้าเอกสารถูกดำเนินการไปแล้ว, ให้แสดงแค่ปุ่มกลับคืน --}}
                        <div class="mt-6 pt-4 border-t text-right">
                            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                ກັບຄືນ
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

