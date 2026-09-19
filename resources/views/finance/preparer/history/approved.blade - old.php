<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ປະວັດເອກະສານທີ່ໄດ້ກວດສອບຜ່ານ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- สามารถเพิ่มฟอร์มค้นหา/กรองได้ที่นี่ --}}
                    <x-document-filter-form 
                        :action="route('finance.preparer.history.approved')" 
                        :departments="$departments"
                        :statuses="$statuses" {{-- ส่งข้อมูลสถานะไปด้วย --}}
                        title-span="md:col-span-2"
                    />
                    <table class="min-w-full divide-y divide-gray-200">
                        {{-- ... (โค้ดหัวตารางเหมือนกับหน้า Dashboard) ... --}}
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ລະຫັດເອກະສານ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຫົວຂໍ້ເອກະສານ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ສະຖານະ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ພາກສ່ວນ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ວັນທີສົ່ງ</th>
                                <th class="relative px-6 py-3 text-center text-base font-bold">ການດໍາເນີນການ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($documents as $document)
                                <tr>
                                    {{-- ... (แสดงข้อมูลเอกสาร) ... --}}
                                    <td class="px-6 py-4 text-center font-mono text-sm">{{ $document->document_code }}</td>
                                    <td class="px-6 py-4">{{ $document->title }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 ... {{ getStatusColorClass($document->status) }}">
                                            {{ translateStatus($document->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $document->requester->department->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('finance.preparer.documents.show', $document->id) }}" class="text-indigo-600 hover:text-indigo-900">ເບິ່ງລາຍລະອຽດ</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        ບໍ່ມີປະວັດເອກະສານທີ່ໄດ້ກວດສອບ
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