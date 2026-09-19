<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ເອກະສານຂອງ') }} {{ $departmentName }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('staff.documents.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            + ສ້າງເອກະສານໃໝ່
                        </a>
                    </div>
                    {{-- เรียกใช้ Component --}}
                    <x-document-filter-form 
                        :action="route('staff.documents.index')" 
                        :statuses="$statuses"
                        title-span="md:col-span-3" {{-- ปรับขนาดตามความเหมาะสม --}}
                    />
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ລະຫັດເອກະສານ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຫົວຂໍ້ເອກະສານ</th>
                                <!--<th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຜູ້ສ້າງ</th>-->
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ປະເພດ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ສະຖານະ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ວັນທີສ້າງ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ພີມ</th>
                                <th class="relative px-6 py-3 text-center text-base font-bold"><span class="sr-only">Actions</span>ການດໍາເນີນການ</th>
                            </tr>
                        </thead>
                        
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($documents as $document)
                            {{-- ถ้าเป็นฉบับร่าง, ให้พื้นหลังของแถวเป็นสีเทาอ่อน --}}
                            <tr class="{{ $document->status == 'DRAFT' ? 'bg-gray-50' : ($document->status == 'REJECTED' ? 'bg-red-50' : '') }}">
            
                                {{-- ... (คอลัมน์ รหัส, หัวข้อ, ผู้สร้าง, ประเภท เหมือนเดิม) ... --}}
                                <td class="px-6 py-4 font-mono text-sm">{{ $document->document_code }}</td>
                                <td class="px-6 py-4">{{ $document->title }}</td>
                                <!--<td class="px-6 py-4">{{ $document->requester->displayName ?? 'N/A' }}</td>-->
                                <td class="px-6 py-4">{{ $document->documentType->name ?? 'N/A' }}</td>

                                <td class="px-6 py-4">
                                    {{-- แสดง Status Badge ตามสถานะ --}}
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                        {{ translateStatus($document->status) }}
                                    </span>
                                </td>
            
                                <td class="px-6 py-4">{{ $document->created_at->format('d/m/Y H:i') }}</td>
            
                                <td class="px-6 py-4 text-center">
                                    {{-- ปุ่มพิมพ์ (แสดงเฉพาะเมื่อมีรหัสเอกสารแล้ว) --}}
                                    @if($document->document_code)
                                        <a href="{{ route('staff.documents.print', $document->id) }}" target="_blank" title="ພີມເອກະສານ">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 hover:text-blue-600 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                    {{-- ===== Logic การแสดงปุ่ม Actions ใหม่ ===== --}}                
                                    {{-- ถ้าเป็น DRAFT หรือ REJECTED, ให้แสดงปุ่ม "แก้ไข" --}}
                                    @if(in_array($document->status, ['DRAFT', 'REJECTED']))
                                        <a href="{{ route('staff.documents.edit', $document->id) }}" class="text-blue-600 hover:text-blue-900 font-semibold">
                                            ແກ້ໄຂ
                                        </a>
                                    @else
                                        {{-- ถ้าสถานะอื่น, ให้แสดงปุ่ม "ดูรายละเอียด" --}}
                                        <a href="{{ route('staff.documents.show', $document->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                            ເບິ່ງລາຍລະອຽດ
                                        </a>
                                    @endif

                                    {{-- ===== เพิ่ม Logic ปุ่ม "ส่ง" และ "ลบ" ที่นี่ ===== --}}
                                    @if($document->status === 'DRAFT')
                                        {{-- ปุ่ม "ส่ง" (สีเขียว) --}}
                                        <form action="{{ route('staff.documents.submit', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານຕ້ອງການສົ່ງເອກະສານສະບັບນີ້ແມ່ນບໍ?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-green-600 hover:text-green-900 font-semibold">ສົ່ງ</button>
                                        </form>

                                        {{-- ปุ่ม "ลบ" (สีแดง) --}}
                                        <form action="{{ route('staff.documents.destroy', $document->id) }}" method="POST" class="inline-block" onsubmit="return confirm('ທ່ານແນ່ໃນບໍວ່າຕ້ອງການລຶບເອກະສານສະບັບຮ່າງນີ້?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">ລຶບ</button>
                                        </form>
                                    @endif

                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                        ທ່ານບໍ່ມີເອກະສານ
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

