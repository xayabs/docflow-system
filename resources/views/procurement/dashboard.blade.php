<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ເອກະສານຂອງ ຝ່າຍຈັດຊື້/ສ້ອມແປງ') }} 
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
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
                    <h3 class="text-lg font-medium mb-4">ລາຍການເອກະສານທີ່ລໍຖ້າການປະມູນ</h3>
                    <x-document-filter-form 
                        :action="route('procurement.dashboard')" 
                        :departments="$departments"
                        :statuses="$statuses"
                        title-span="md:col-span-2"
                    />
                    {{-- (ໂຄດຕາຕະລາງຄືກັນກັບຂອງເລຂາ, ພຽງແຕ່ປ່ຽນຊື່ຕົວແປ ແລະ route) --}}
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ລະຫັດເອກະສານ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຫົວຂໍ້ເອກະສານ</th>
                                <!--<th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຜູ້ສ້າງ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ປະເພດ</th>-->
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ສະຖານະ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ພາກສ່ວນສະເໜີ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ວັນທີສ້າງ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ພີມ</th> 
                                <th class="relative px-6 py-3 text-center text-base font-bold">ການດໍາເນີນການ</th>
                            </tr>
                        </thead>
                        {{-- ... (ຫົວຕາຕະລາງ) ... --}}
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($documents as $document)
                                <tr>
                                    <td class="px-6 py-4 text-center font-mono text-sm">{{ $document->document_code }}</td>
                                    <td class="px-6 py-4">{{ $document->title }}</td>
                                    <!--<td class="px-6 py-4">{{ $document->requester->displayName ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $document->documentType->name ?? 'N/A' }}</td>-->
                                    <td class="px-6 py-4">
                                        {{-- ເຮົາຈະມາເຮັດ Status Badge ແບບງາມໆ ໃນພາຍຫຼັງ --}}
                                        <!--
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ $document->status }}-->
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                            {{ translateStatus($document->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $document->requester->department->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                    {{-- ===== เพิ่ม Cell ของปุ่ม "พิมพ์" ที่นี่ ===== --}}
                                    <td class="px-6 py-4 text-center">
                                        {{-- แสดงไอคอนพิมพ์ก็ต่อเมื่อเป็นเอกสารขอถอนเงิน --}}
                                        @if($document->document_type_id == 1)
                                        <a href="{{ route('procurement.documents.print', $document->id) }}" target="_blank" title="พิมพ์เอกสาร">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 hover:text-blue-600 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a>
                                        @else
                                            - {{-- ถ้าเป็นเอกสารจัดซื้อ, ให้แสดงขีด --}}
                                        @endif
                                    </td>
                                    <!--
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">

                                    {{-- =Logic การแสดงปุ่ม Actions ใหม่= --}}
    
                                    {{-- ถ้าเป็นฉบับร่าง (DRAFT) ที่ฝ่ายจัดซื้อสร้าง --}}
                                    @if($document->status === 'DRAFT' && $document->requester_id === auth()->id())
        
                                        {{-- ลิงก์ "แก้ไข" --}}
                                        <a href="{{ route('procurement.documents.edit', $document->id) }}" class="text-blue-600 hover:text-blue-900 font-semibold">
                                            ແກ້ໄຂ
                                        </a>
        
                                            {{-- ปุ่ม "ส่ง" --}}
                                            <form action="{{ route('procurement.documents.submit', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານຕ້ອງການສົ່ງເອກະສານນີ້ແມ່ນບໍ?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-green-600 hover:text-green-900 font-semibold">ສົ່ງ</button>
                                        </form>

                                        {{-- ปุ่ม "ลบ" --}}
                                        <form action="{{ route('procurement.documents.destroy', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານແນ່ໃນບໍ່ວ່າຈະລຶບເອກະສານສະບັບຮ່າງນີ້?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">ລຶບ</button>
                                        </form>
    
                                    @else
                                        {{-- ถ้าเป็นสถานะอื่นๆ, ให้แสดงลิงก์ "ดู/ตรวจสอบ" ตามปกติ --}}
                                        <a href="{{ route('procurement.documents.show', $document->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                            @if($document->parent_document_id !== null)
                                                ເບິ່ງລາຍລະອຽດ
                                            @else
                                                ກວດສອບ ແລະ ດຳເນີນການ
                                            @endif
                                        </a>
                                    @endif
                                    </td>
                                    -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                        {{-- ถ้าสถานะเป็น DRAFT หรือ REJECTED --}}
                                        @if(in_array($document->status, ['DRAFT', 'REJECTED']))
        
                                            {{-- และต้องเป็นเอกสารที่ฝ่ายจัดซื้อสร้างเอง --}}
                                            @if($document->requester_id === auth()->id())

                                                {{-- ลิงก์ "แก้ไข" (แสดงทั้ง DRAFT และ REJECTED) --}}
                                                <a href="{{ route('procurement.documents.edit', $document->id) }}" class="text-blue-600 hover:text-blue-900 font-semibold">
                                                    ແກ້ໄຂ
                                                </a>
            
                                                {{-- ถ้าเป็น DRAFT, ให้มีปุ่ม "ส่ง" และ "ลบ" --}}
                                                @if($document->status === 'DRAFT')
                                                    <form action="{{ route('procurement.documents.submit', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('คุณต้องการส่งเอกสารนี้ใช่หรือไม่?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-green-600 hover:text-green-900 font-semibold">ສົ່ງ</button>
                                                    </form>

                                                    <form action="{{ route('procurement.documents.destroy', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าจะลบฉบับร่างนี้?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">ລຶບ</button>
                                                    </form>
                                                @endif

                                            @else
                                                {{-- กรณีที่เป็นเอกสารจัดซื้อที่ถูก REJECTED (คนสร้างคือ Staff) --}}
                                                <a href="{{ route('procurement.documents.show', $document->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    ກວດສອບ ແລະ ດຳເນີນການ
                                                </a>
                                            @endif

                                        @else
                                        {{-- ถ้าเป็นสถานะอื่นๆ (งานที่ต้องทำ หรือ เอกสารที่ติดตามที่อยู่ใน Workflow) --}}
                                            <a href="{{ route('procurement.documents.show', $document->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                @if($document->parent_document_id !== null)
                                                    ເບິ່ງລາຍລະອຽດ
                                                @else
                                                    ກວດສອບ ແລະ ດຳເນີນການ
                                                @endif
                                            </a>
                                        @endif

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        ບໍ່ມີເອກະສານທີ່ລໍຖ້າການປະມູນ
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $documents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>