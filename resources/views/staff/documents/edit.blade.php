<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ແກ້ໄຂເອກະສານ:') }} {{ $document->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" x-data="{ documentTypeId: '{{ old('document_type_id', $document->document_type_id) }}' }">
                    
                    {{-- แสดงเหตุผลที่ถูกปฏิเสธ --}}
                    @if($document->status == 'REJECTED' && $document->rejected_reason)
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                            <p class="font-bold">ເຫດຜົນທີ່ຖືກສົ່ງກັບມາແກ້ໄຂ:</p>
                            <p>{{ $document->rejected_reason }}</p>
                        </div>
                    @endif

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

                    @php
                        $userRole = auth()->user()->role->name;
                        // สร้างตัวแปรเก็บชื่อ Route เพื่อให้โค้ดสะอาด
                        $updateRoute = '';
                        $cancelRoute = '';

                        if (auth()->user()->role->name === 'Staff') {
                            $updateRoute = route('staff.documents.update', $document->id);
                            $cancelRoute = route('staff.documents.index');
                        } elseif (auth()->user()->role->name === 'Procurement_Staff') {
                            $updateRoute = route('procurement.documents.update', $document->id);
                            $cancelRoute = route('procurement.dashboard');
                        }
                    @endphp

                    <form method="POST" action="{{ $updateRoute }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PATCH')

                        <!-- Document Details -->
                        <div class="grid grid-cols-3 gap-6">
                            <div class="col-span-2">
                                <x-input-label for="title" value="ຫົວຂໍ້ເລື່ອງ / ໂຄງການ" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $document->title)" required />
                            </div>
                            <div>
                                <x-input-label for="document_type_id" value="ປະເພດເອກະສານ" />
                                <select name="document_type_id" id="document_type_id" x-model="documentTypeId" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">-- ເລືອກປະເພດ --</option>
                                    @foreach ($documentTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('document_type_id', $document->document_type_id) == $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="references" value="ອິງຕາມ (ຖ້າມີ, ແຕ່ລະຂໍ້ຂຶ້ນແຖວໃໝ່)" />
                            <textarea id="references" name="references" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('references', $document->references) }}</textarea>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="activity_description" value="ເນື້ອໃນກິດຈະກຳທີ່ຈະປະຕິບັດ" />
                            <textarea id="activity_description" name="activity_description" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('activity_description', $document->activity_description) }}</textarea>
                        </div>

                        <!-- Document Items Section -->
                        <div class="mt-8" x-show="documentTypeId == '1'" 
                            x-data="{ items: {{ old('items') ? json_encode(old('items')) : $document->documentItems->toJson() }} }">
                            <h3 class="text-lg font-medium">ລາຍການເບີກຈ່າຍ</h3>
                            <div class="mt-4 border-t border-b border-gray-200 divide-y divide-gray-200">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-12 gap-4 p-3 items-center">
                                        <!-- Fields for items -->
                                        <div class="col-span-5"><label :for="'description_' + index" class="block font-medium text-sm text-gray-700">ລາຍລະອຽດ</label>
                                        <input :id="'description_' + index" type="text" x-model="item.item_description" :name="'items[' + index + '][item_description]'" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1" :required="documentTypeId == '1'">
                                        </div>
                                        <div class="col-span-2"><label :for="'quantity_' + index" class="block font-medium text-sm text-gray-700">ຈຳນວນ</label><input :id="'quantity_' + index" type="number" x-model="item.quantity" :name="'items[' + index + '][quantity]'" class="border-gray-300 rounded-md shadow-sm w-full mt-1" min="1" :required="documentTypeId == '1'"></div>
                                        <div class="col-span-2"><label :for="'unit_price_' + index" class="block font-medium text-sm text-gray-700">ລາຄາຕໍ່ໜ່ວຍ</label><input :id="'unit_price_' + index" type="number" x-model="item.unit_price" :name="'items[' + index + '][unit_price]'" class="border-gray-300 rounded-md shadow-sm w-full mt-1" min="0" step="0.01" :required="documentTypeId == '1'"></div>
                                        <div class="col-span-2"><label class="block font-medium text-sm text-gray-700">ລາຄາລວມ</label><p class="mt-2" x-text="(item.quantity * item.unit_price).toFixed(2)"></p></div>
                                        <div class="col-span-1"><button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="text-red-500 mt-6">&times; ລຶບ</button></div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="items.push({ description: '', quantity: 1, unit_price: 0 })" class="mt-4 text-blue-500">+ ເພີ່ມລາຍການ</button>
                        </div>

                        <!-- Attachments -->
                        <div class="mt-8">
                            <x-input-label for="attachments" value="ໄຟລ໌ແນບ (ເລືອກໃໝ່ຖ້າຕ້ອງການປ່ຽນ)" />
                            <input id="attachments" type="file" name="attachments[]" multiple class="block mt-1 w-full ..."/>
                            {{-- (Optional) ສະແດງຟາຍແນບເກົ່າ --}}
                        </div>

                        <div class="flex items-center justify-end mt-8 space-x-4">
                            <a href="{{ $cancelRoute }}" class="text-sm text-gray-600 hover:text-gray-900">
                                ຍົກເລີກ
                            </a>
    
                            {{-- แสดงปุ่ม "บันทึกฉบับร่าง" ก็ต่อเมื่อสถานะเป็น DRAFT, REJECTED --}}<!--
                            @if($document->status === 'DRAFT')
                                <button type="submit" name="action" value="save_draft" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    ບັນທຶກສະບັບຮ່າງ
                                </button>
                            @endif-->
                            @if(in_array($document->status, ['DRAFT', 'REJECTED']))
                                <button type="submit" name="action" value="save_draft"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    ບັນທຶກສະບັບຮ່າງ
                                </button>
                            @endif

                            {{-- ปุ่ม "ส่ง" จะแสดงสำหรับทั้ง DRAFT และ REJECTED --}}
                            <button type="submit" name="action" value="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{-- เปลี่ยนข้อความปุ่มตามสถานะ --}}
                                {{ $document->status === 'REJECTED' ? 'ອັບເດດ ແລະ ສົ່ງໃໝ່' : 'ບັນທຶກແລະສົ່ງ' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-app-layout>
