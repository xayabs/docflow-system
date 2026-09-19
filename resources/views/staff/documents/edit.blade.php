<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ແກ້ໄຂເອກະສານ:') }} {{ $document->title }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                <div class="p-4 sm:p-6 text-gray-900" x-data="{ documentTypeId: '{{ old('document_type_id', $document->document_type_id) }}' }">
                    
                    {{-- ສະແດງເຫດຜົນທີ່ຖືກປະຕິເສດ --}}
                    @if($document->status == 'REJECTED' && $document->rejected_reason)
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                            <p class="font-bold text-red-800 text-sm">ເຫດຜົນທີ່ຖືກສົ່ງກັບມາແກ້ໄຂ:</p>
                            <p class="text-red-700 text-sm mt-1">{{ $document->rejected_reason }}</p>
                        </div>
                    @endif

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

                    @php
                        $userRole = auth()->user()->role->name;
                        $updateRoute = '';
                        $cancelRoute = '';

                        if ($userRole === 'Staff') {
                            $updateRoute = route('staff.documents.update', $document->id);
                            $cancelRoute = route('staff.documents.index');
                        } elseif ($userRole === 'Procurement_Staff') {
                            $updateRoute = route('procurement.documents.update', $document->id);
                            $cancelRoute = route('procurement.dashboard');
                        }
                    @endphp

                    <form method="POST" action="{{ $updateRoute }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PATCH')

                        <!-- Document Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                            <div class="md:col-span-2">
                                <x-input-label for="title" value="ຫົວຂໍ້ເລື່ອງ / ໂຄງການ" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $document->title)" required />
                            </div>

                            <div class="md:col-span-1">
                                <x-input-label for="document_type_id" value="ປະເພດເອກະສານ" />
                                <select name="document_type_id" id="document_type_id" x-model="documentTypeId" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">-- ເລືອກປະເພດ --</option>
                                    @foreach ($documentTypes as $type)
                                        <option value="{{ $type->id }}" @selected(old('document_type_id', $document->document_type_id) == $type->id)>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-3">
                                <x-input-label for="references" value="ອິງຕາມ (ຖ້າມີ, ແຕ່ລະຂໍ້ຂຶ້ນແຖວໃໝ່)" />
                                <textarea id="references" name="references" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('references', $document->references) }}</textarea>
                            </div>

                            <div class="md:col-span-3">
                                <x-input-label for="activity_description" value="ເນື້ອໃນກິດຈະກຳທີ່ຈະປະຕິບັດ" />
                                <textarea id="activity_description" name="activity_description" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('activity_description', $document->activity_description) }}</textarea>
                            </div>
                        </div>

                        <!-- Document Items Section with Alpine.js -->
                        <div class="mt-8" x-show="documentTypeId == '1'" 
                            x-data="{ 
                                items: {{ old('items') ? json_encode(old('items')) : $document->documentItems->toJson() }},
                                
                                formatNumber(number) {
                                    if (!number && number !== 0) return '';
                                    let numStr = number.toString().replace(/[^\d.]/g, ''); 
                                    let parts = numStr.split('.');
                                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); 
                                    return parts.join('.');
                                },

                                parseNumber(str) {
                                    if (!str && str !== 0) return 0;
                                    return parseFloat(str.toString().replace(/,/g, '')) || 0;
                                },

                                init() {
                                    this.items.forEach(item => {
                                        if (item.quantity) {
                                            item.quantity = Math.round(this.parseNumber(item.quantity));
                                        }

                                        if (item.unit_price) {
                                            let roundedPrice = Math.round(this.parseNumber(item.unit_price));
                                            item.unit_price = this.formatNumber(roundedPrice);
                                        }
                                    });
                                }
                            }">
                            
                            <h3 class="text-base md:text-lg font-bold border-b pb-2 text-gray-800">ລາຍການເບີກຈ່າຍ</h3>
                            
                            <div class="mt-4 space-y-4 md:space-y-0 md:border-t md:border-b md:border-gray-200 md:divide-y md:divide-gray-200">
                                <template x-for="(item, index) in items" :key="index">
                                    {{-- Responsive Item Box: ໃນມືຖືຈະເປັນ Card ສີເທົາອ່ອນ, ໃນຄອມຈະເປັນແຖວຕາຕະລາງ --}}
                                    <div class="p-4 bg-gray-50 md:bg-transparent rounded-xl border border-gray-200 md:border-0 md:p-3 grid grid-cols-1 sm:grid-cols-12 gap-3 md:gap-4 items-center relative">
                                        
                                        <!-- Description -->
                                        <div class="sm:col-span-5">
                                            <label :for="'description_' + index" class="block font-medium text-xs sm:text-sm text-gray-700">ລາຍລະອຽດ</label>
                                            <input :id="'description_' + index" type="text" x-model="item.item_description" :name="'items[' + index + '][item_description]'" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1 text-sm" placeholder="ພິມລາຍການ..." :required="documentTypeId == '1'">
                                        </div>
                                        
                                        <!-- Quantity & Unit Price -->
                                        <div class="sm:col-span-4 grid grid-cols-2 gap-2">
                                            <div>
                                                <label :for="'quantity_' + index" class="block font-medium text-xs sm:text-sm text-gray-700">ຈຳນວນ</label>
                                                <input :id="'quantity_' + index" type="number" x-model="item.quantity" :name="'items[' + index + '][quantity]'" class="border-gray-300 rounded-md shadow-sm w-full mt-1 text-sm text-right" min="1" :required="documentTypeId == '1'">
                                            </div>
                                            <div>
                                                <label :for="'unit_price_' + index" class="block font-medium text-xs sm:text-sm text-gray-700">ລາຄາຕໍ່ໜ່ວຍ</label>
                                                <input :id="'unit_price_' + index" 
                                                       type="text" 
                                                       x-model="item.unit_price" 
                                                       @input="item.unit_price = formatNumber($event.target.value)"
                                                       class="border-gray-300 rounded-md shadow-sm w-full mt-1 text-sm text-right" 
                                                       placeholder="0"
                                                       :required="documentTypeId == '1'">
                                                <input type="hidden" :name="'items[' + index + '][unit_price]'" :value="parseNumber(item.unit_price)">
                                            </div>
                                        </div>
                                        
                                        <!-- Total Price & Delete Button -->
                                        <div class="sm:col-span-3 flex justify-between items-center sm:grid sm:grid-cols-3 sm:gap-2 pt-2 sm:pt-0 border-t sm:border-0 border-gray-200">
                                            <div class="sm:col-span-2 text-left sm:text-right">
                                                <span class="block font-medium text-xs text-gray-500 sm:hidden">ລາຄາລວມ:</span>
                                                <p class="font-bold text-sm sm:text-base text-blue-600 sm:text-gray-900" x-text="Math.round(item.quantity * parseNumber(item.unit_price)).toLocaleString('en-US') + ' ກີບ'"></p>
                                            </div>
                                            <div class="sm:col-span-1 text-right">
                                                <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="text-red-500 hover:text-red-700 text-xs sm:text-sm font-semibold p-1 rounded hover:bg-red-50">
                                                    &times; ລຶບ
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </template>
                            </div>
                            
                            <button type="button" @click="items.push({ item_description: '', quantity: 1, unit_price: '' })" class="mt-4 inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800">
                                + ເພີ່ມລາຍການໃໝ່
                            </button>
                        </div>

                        <!-- Attachments -->
                        <div class="mt-8">
                            <x-input-label for="attachments" value="ໄຟລ໌ແນບ (ເລືອກໃໝ່ຖ້າຕ້ອງການປ່ຽນ)" />
                            <input id="attachments" type="file" name="attachments[]" multiple class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end mt-8 gap-3 sm:space-x-4">
                            <a href="{{ $cancelRoute }}" class="w-full sm:w-auto text-center py-2 text-sm text-gray-600 hover:text-gray-900">
                                ຍົກເລີກ
                            </a>

                            @if(in_array($document->status, ['DRAFT', 'REJECTED']))
                                <button type="submit" name="action" value="save_draft"
                                    class="w-full sm:w-auto justify-center inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                    ບັນທຶກສະບັບຮ່າງ
                                </button>
                            @endif

                            <button type="submit" name="action" value="submit" 
                                class="w-full sm:w-auto justify-center inline-flex items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                {{ $document->status === 'REJECTED' ? 'ອັບເດດ ແລະ ສົ່ງໃໝ່' : 'ບັນທຶກແລະສົ່ງ' }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>