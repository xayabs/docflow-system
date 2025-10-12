<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ລາຍງານເອກະສານທັງໝົດ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            {{-- Filter Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">ຄົ້ນຫາຂໍ້ມູນເອກະສານ</h3>
                    {{-- ເຮົາຈະໃຊ້ route() ເດີມ ເພາະ action GET ຈະສົ່ງຄ່າໄປທີ່ URL ປະຈຸບັນ --}}
                    <form action="{{ route('reports.documents.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <div class="md:col-span-2">
                                <label for="start_date" class="block text-sm font-medium text-gray-700">ວັນທີເລີ່ມຕົ້ນ</label>
                                <input type="text" name="start_date" id="start_date" value="{{ request('start_date') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm datepicker"
                                placeholder="ວັນທີ/ເດືອນ/ປີ"
                                x-data x-init="initDatepicker($el)">
                            </div>

                            <div class="md:col-span-2">
                                <label for="end_date" class="block text-sm font-medium text-gray-700">ວັນທີສີ້ນສຸດ</label>
                                <input type="text" name="end_date" id="end_date" value="{{ request('end_date') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm datepicker"
                                placeholder="ວັນທີ/ເດືອນ/ປີ"
                                x-data x-init="initDatepicker($el)">
                            </div>

                            <div class="md:col-span-3">
                                <label for="department_id" class="block text-sm font-medium text-gray-700">ພາກວິຊາ/ພະແນກ</label>
                                <select name="department_id" id="department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- ທຸກພາກສ່ວນ --</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label for="status" class="block text-sm font-medium text-gray-700">ສະຖານະພາບ</label>
                                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- ທຸກສະຖານະພາບ --</option>
                                    @foreach ($statuses as $statusCode => $statusName)
                                        <option value="{{ $statusCode }}" @selected(request('status') == $statusCode)>
                                            {{ $statusName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-3 flex items-center space-x-2">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md">ຄົ້ນຫາຂໍ້ມູນ</button>
                                <a href="{{ route('reports.documents.export', request()->query()) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white rounded-md">
                                    ສົ່ງອອກເປັນ Excel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Results Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">ຜົນໄດ້ຮັບ ({{ $documents->total() }} ລາຍການ)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ລະຫັດເອກະສານ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຫົວຂໍ້ເອກະສານ</th>
                                    <!--<th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ຜູ້ຮ້ອງຂໍ</th>-->
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ພາກສ່ວນສະເໜີ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500">ຍອດເງິນລວມ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500">ສະຖານະພາບ</th>
                                    <th class="px-6 py-3 text-center text-base font-bold text-gray-500 uppercase">ວັນທີສົ່ງ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($documents as $document)
                                    <tr>
                                        <td class="px-6 py-4 text-center font-mono text-center text-sm">{{ $document->document_code }}</td>
                                        <td class="px-6 py-4">{{ $document->title }}</td>
                                        <!--<td class="px-6 py-4">{{ $document->requester->displayName ?? '' }}</td>-->
                                        <td class="px-6 py-4">{{ $document->department->name ?? '' }}</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($document->total_amount, 2) }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                                {{ translateStatus($document->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">{{ $document->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">ບໍ່ພົບຂໍ້ມູນຕາມເງື່ອນໄຂທີ່ກໍານົດ</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination Links --}}
                    <div class="p-6">
                        {{ $documents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>