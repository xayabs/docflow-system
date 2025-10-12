<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ສ້າງເອກະສານໃໝ່') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- ຂັ້ນຕອນທີ 1: ເພີ່ມ x-data ທີ່ div ຫຼັກ ເພື່ອກຳນົດຄ່າເລີ່ມຕົ້ນໃຫ້ກັບຕົວແປ --}}
                <div class="p-6 text-gray-900" x-data="{ documentTypeId: '{{ old('document_type_id', isset($purchaseDocument) ? 1 : '') }}' }">
                    
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

                    <form method="POST" action="{{ isset($purchaseDocument) ? route('procurement.documents.storePaymentRequest') : route('staff.documents.store') }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        @if(isset($purchaseDocument))
                            <input type="hidden" name="parent_document_id" value="{{ $purchaseDocument->id }}">
                        @endif
                        <!-- Document Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="col-span-2">
                                <x-input-label for="title" value="ຫົວຂໍ້ເລື່ອງ / ໂຄງການ" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', isset($purchaseDocument) ? 'ຂໍຖອນເງິນສໍາຫຼັບ ' . $purchaseDocument->title : '')" required />
                            </div>
                            <div class="col-span-1">
                                <x-input-label for="document_type_id" value="ປະເພດເອກະສານ" />
                                {{-- ຂັ້ນຕອນທີ 2: ເພີ່ມ x-model ເພື່ອເຊື່ອມຕໍ່ Dropdown ກັບຕົວແປ documentTypeId --}}
                                <select name="document_type_id" id="document_type_id" x-model="documentTypeId" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" @if(isset($purchaseDocument)) disabled @endif required> {{-- ປິດການແກ້ໄຂ --}}
                                    <option value="">-- ເລືອກປະເພດ --</option>
                                    @foreach ($documentTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{-- ກວດສອບວ່າຄວນຈະ selected ຫຼືບໍ່ --}}
                                            @if( (isset($purchaseDocument) && $type->id == 1) || old('document_type_id') == $type->id )
                                                selected
                                            @endif
                                        >
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- ຖ້າຖືກ disabled, select ຈະບໍ່ສົ່ງຄ່າໄປ. ເຮົາຈຶ່ງຕ້ອງເພີ່ມ hidden input ນີ້. --}}
                                @if(isset($purchaseDocument))
                                    <input type="hidden" name="document_type_id" value="1">
                                @endif
                            </div>
                            <div class="mt-4">
                                <x-input-label for="references" value="ອິງຕາມ (ຖ້າມີ, ແຕ່ລະຂໍ້ຂຶ້ນແຖວໃໝ່)" />
                                <textarea id="references" name="references" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('references') }}</textarea>
                            </div>
                            <div class="mt-4">
                                <x-input-label for="activity_description" value="ເນື້ອໃນກິດຈະກຳທີ່ຈະປະຕິບັດ" />
                                <textarea id="activity_description" name="activity_description" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('activity_description') }}</textarea>
                            </div>
                        </div>

                        {{-- ຂັ້ນຕອນທີ 3: ເພີ່ມ x-show ເພື່ອສ້າງເງື່ອນໄຂໃນການສະແດງຜົນ --}}
                        {{-- ສ່ວນນີ້ຈະສະແດງ ກໍຕໍ່ເມື່ອ documentTypeId ມີຄ່າເທົ່າກັບ '1' (ID ຂອງ "ຂໍຖອນເງິນ") --}}
                        <div class="mt-8" x-show="documentTypeId == '1'" x-data="{items: {{ isset($purchaseDocument) ? $purchaseDocument->documentItems->toJson() : '[{ description: \'\', quantity: 1, unit_price: 0 }]' }} }">
                            <h3 class="text-lg font-medium">ລາຍການເບີກຈ່າຍ</h3>
                            <div class="mt-4 border-t border-b border-gray-200 divide-y divide-gray-200">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-12 gap-4 p-3 items-center">
                                        <!-- Description -->
                                        <div class="col-span-5">
                                            <label :for="'description_' + index" class="block font-medium text-sm text-gray-700">ລາຍລະອຽດ</label>
                                            <input :id="'description_' + index" type="text" x-model="item.description" :name="'items[' + index + '][item_description]'" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1" :required="documentTypeId == '1'">
                                        </div>
                                        <!-- ... (Input ອື່ນໆຂອງ Items ຄືເກົ່າ) ... -->
                                        <div class="col-span-2">
                                            <label :for="'quantity_' + index" class="block font-medium text-sm text-gray-700">ຈຳນວນ</label>
                                            <input :id="'quantity_' + index" type="number" x-model="item.quantity" :name="'items[' + index + '][quantity]'" class="border-gray-300 rounded-md shadow-sm w-full mt-1" min="1" :required="documentTypeId == '1'">
                                        </div>
                                        <div class="col-span-2">
                                            <label :for="'unit_price_' + index" class="block font-medium text-sm text-gray-700">ລາຄາຕໍ່ໜ່ວຍ</label>
                                            <input :id="'unit_price_' + index" type="number" x-model="item.unit_price" :name="'items[' + index + '][unit_price]'" class="border-gray-300 rounded-md shadow-sm w-full mt-1" min="0" step="0.01" :required="documentTypeId == '1'">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block font-medium text-sm text-gray-700">ລາຄາລວມ</label>
                                            <p class="mt-2" x-text="(item.quantity * item.unit_price).toFixed(2)"></p>
                                        </div>
                                        <div class="col-span-1">
                                            <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="text-red-500 mt-6">&times; ລຶບ</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="items.push({ description: '', quantity: 1, unit_price: 0 })" class="mt-4 text-blue-500">+ ເພີ່ມລາຍການ</button>
                        </div>
                        
                        {{-- ໃນອະນາຄົດ, ເຮົາສາມາດເພີ່ມ div ໃຫມ່ທີ່ມີ x-show="documentTypeId == '2'" ຢູ່ທີ່ນີ້ໄດ້ --}}

                        <!-- Attachments -->
                        <div class="mt-8">
                            <x-input-label for="attachments" value="ໄຟລ໌ແນບ (ຖ້າມີ)" />
                            <input id="attachments" type="file" name="attachments[]" multiple class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                        </div>
                        <!--
                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('staff.documents.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">
                                ຍົກເລີກ
                            </a>
                            <x-primary-button class="ms-4">
                                ບັນທຶກແລະສົ່ງ
                            </x-primary-button>
                        </div>
                        <a href="{{ route('staff.documents.index') }}" class="text-sm text-gray-600 hover:text-gray-900">ຍົກເລີກ</a>
                        -->
                        <div class="flex items-center justify-end mt-8 space-x-4">
                            <a href="{{ url()->previous() }}" class="text-sm text-gray-600 hover:text-gray-900">ຍົກເລີກ</a>

                            {{-- ปุ่ม "บันทึกฉบับร่าง" (สีเทา) --}}
                            <button type="submit" name="action" value="save_draft"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                ບັນທຶກສະບັບຮ່າງ
                                </button>
    
                            {{-- ปุ่ม "บันทึกและส่ง" (สีน้ำเงิน) --}}
                            <button type="submit" name="action" value="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                ບັນທຶກແລະສົ່ງ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-app-layout>