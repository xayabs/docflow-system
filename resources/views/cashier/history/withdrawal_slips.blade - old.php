<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ເອກະສານທີ່ຕີໃບຖອນແລ້ວ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">ລາຍການເອກະສານທີ່ໄດ້ຂຽນໃບຖອນແລ້ວ</h3>
                    
                    {{-- ຟອມກັ່ນຕອງຄົ້ນຫາ --}}
                    <x-document-filter-form 
                        :action="route('cashier.history.withdrawalSlips')" 
                        :departments="collect()"
                        title-span="md:col-span-3" 
                    />

                    <table class="min-w-full divide-y divide-gray-200 mt-4">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500">ລະຫັດເອກະສານ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500">ຫົວຂໍ້ເອກະສານ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500">ຜູ້ຮ້ອງຂໍ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500">ມູນຄ່າລວມ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500">ສະຖານະປັດຈຸບັນ</th>
                                <th class="relative px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($documents as $document)
                                <tr>
                                    <td class="px-6 py-4 text-center font-mono text-sm">{{ $document->document_code }}</td>
                                    <td class="px-6 py-4">{{ $document->title }}</td>
                                    <td class="px-6 py-4">{{ $document->requester->displayName ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 font-semibold">{{ number_format($document->total_amount, 0) }} KIP</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                            {{ translateStatus($document->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('cashier.documents.show', $document->id) }}" class="text-indigo-600 hover:text-indigo-900">ເບິ່ງລາຍລະອຽດ</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        ບໍ່ມີລາຍການທີ່ໄດ້ຂຽນໃບຖອນແລ້ວ
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