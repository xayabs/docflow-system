<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ລາຍງານເອກະສານທັງໝົດ') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
            {{-- Filter Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg rounded-xl border border-gray-200 mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">ຄົ້ນຫາຂໍ້ມູນເອກະສານ</h3>
                    <form action="{{ route('reports.documents.index') }}" method="GET">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                            <!-- Date Inputs -->
                            <div>
                                <x-input-label for="start_date" value="ວັນທີເລີ່ມຕົ້ນ" />
                                <input type="text" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm datepicker" placeholder="ວັນທີ/ເດືອນ/ປີ" x-data x-init="initDatepicker($el)">
                            </div>
                            <div>
                                <x-input-label for="end_date" value="ວັນທີສີ້ນສຸດ" />
                                <input type="text" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm datepicker" placeholder="ວັນທີ/ເດືອນ/ປີ" x-data x-init="initDatepicker($el)">
                            </div>
                            <!-- Dropdowns -->
                            <div>
                                <x-input-label for="department_id" value="ພາກສ່ວນ" />
                                <select name="department_id" id="department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- ທຸກພາກສ່ວນ --</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="status" value="ສະຖານະ" />
                                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- ທຸກສະຖານະ --</option>
                                    @foreach ($statuses as $statusCode => $statusName)
                                        <option value="{{ $statusCode }}" @selected(request('status') == $statusCode)>{{ $statusName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Buttons -->
                            <div class="flex space-x-2">
                                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700">ຄົ້ນຫາ</button>
                                <a href="{{ route('reports.documents.export', request()->query()) }}" class="flex-1 text-center px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-700">Excel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Results Table & Cards --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">ຜົນໄດ້ຮັບ ({{ $documents->total() }} ລາຍການ)</h3>
                    
                    {{-- Desktop Table --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">ລະຫັດເອກະສານ</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">ຫົວຂໍ້ເອກະສານ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">ພາກສ່ວນສະເໜີ</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">ມູນຄ່າລວມ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">ສະຖານະ</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">ວັນທີສົ່ງ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($documents as $document)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-center font-mono text-sm">{{ $document->document_code }}</td>
                                        <td class="px-6 py-4 font-medium">{{ $document->title }}</td>
                                        <td class="px-6 py-4 text-center">{{ $document->department->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-right">{{ $document->document_type_id == 2 ? 'N/A' : number_format($document->total_amount, 0) . ' ກີບ' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                                {{ translateStatus($document->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-xs text-gray-500">{{ $document->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">ບໍ່ພົບຂໍ້ມູນ</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="block md:hidden space-y-3">
                        @forelse ($documents as $document)
                            <div class="bg-white border rounded-lg p-4 shadow-sm border-gray-200">
                                <div class="flex justify-between mb-2">
                                    <span class="text-xs font-mono font-bold bg-gray-100 px-2 py-1 rounded">{{ $document->document_code }}</span>
                                    <span class="text-[10px] px-2 py-1 rounded-full {{ getStatusColorClass($document->status) }}">{{ translateStatus($document->status) }}</span>
                                </div>
                                <h4 class="font-bold text-sm">{{ $document->title }}</h4>
                                <div class="mt-2 text-xs text-gray-600 grid grid-cols-2 gap-2">
                                    <p>ພາກສ່ວນ: {{ $document->department->name ?? 'N/A' }}</p>
                                    <p class="text-right">ມູນຄ່າ: {{ number_format($document->total_amount, 0) }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">ບໍ່ພົບຂໍ້ມູນ</div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $documents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>