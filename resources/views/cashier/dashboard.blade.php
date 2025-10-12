<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ເອກະສານຂອງ ຄັງເງິນສົດ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
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
                    <h3 class="text-lg font-medium mb-4">ລາຍການເອກະສານທີ່ລໍຖ້າຈ່າຍເງິນ</h3>
                    <x-document-filter-form 
                        :action="route('cashier.dashboard')" 
                        :departments="$departments"
                        title-span="md:col-span-3" 
                    />
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ລະຫັດເອກະສານ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຫົວຂໍ້ເອກະສານ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຜູ້ຮ້ອງຂໍ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ມູນຄ່າລວມ</th>
                                <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ວັນທີອະນຸມັດ</th>
                                <th class="relative px-6 py-3 text-center text-base font-bold">ການດໍາເນີນການ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($payableDocuments as $document)
                                <tr>
                                    <td class="px-6 py-4 text-center font-mono text-sm">{{ $document->document_code }}</td>
                                    <td class="px-6 py-4">{{ $document->title }}</td>
                                    <td class="px-6 py-4">{{ $document->requester->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 font-semibold">{{ number_format($document->total_amount, 2) }} KIP</td>
                                    <td class="px-6 py-4">{{ $document->updated_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('cashier.documents.show', $document->id) }}" class="text-indigo-600 hover:text-indigo-900">ເບິ່ງ ແລະ ຢືນຢັນການຈ່າຍເງິນ</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        ບໍ່ມີເອກະສານທີ່ລໍຖ້າການຈ່າຍເງິນ
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $payableDocuments->links() }}
                    </div>
                    {{-- ... (Pagination) ... --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

