@props([
    'action', 
    'departments' => null, 
    'statuses' => [],
    'titleSpan' => 'lg:col-span-2' // ປ່ຽນຄ່າ Default ໃຫ້ກວ້າງຂຶ້ນໜ້ອຍໜຶ່ງໃນຈໍໃຫຍ່
])

<div class="mb-6 bg-gray-50/50 p-4 rounded-xl border border-gray-200">
    <form action="{{ $action }}" method="GET">
        
        {{-- ໃຊ້ Grid ທີ່ປັບປ່ຽນຕາມຂະໜາດຈໍ: ມືຖື 1 ຖັນ, ແທັບເລັດ 2-3 ຖັນ, ຄອມ 4-6 ຖັນ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 items-end">
            
            <!-- ຄົ້ນຫາຕາມລະຫັດ -->
            <div class="sm:col-span-1 md:col-span-1 lg:col-span-1">
                <label for="doc_code" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">ລະຫັດເອກະສານ</label>
                <input type="text" name="doc_code" id="doc_code" value="{{ request('doc_code') }}" class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="ຕົວຢ່າງ: 001...">
            </div>

            <!-- ຄົ້ນຫາຕາມຫົວຂໍ້ -->
            <div class="sm:col-span-1 md:col-span-2 {{ $titleSpan }}">
                <label for="title" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">ຫົວຂໍ້ເອກະສານ</label>
                <input type="text" name="title" id="title" value="{{ request('title') }}" class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="ພິມຄຳຄົ້ນຫາ...">
            </div>

            <!-- ກັ່ນຕອງຕາມພາກສ່ວນ -->
            @if(isset($departments) && $departments->isNotEmpty())
                <div class="sm:col-span-1 md:col-span-1 lg:col-span-1">
                    <label for="department_id" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">ພາກສ່ວນສະເໜີ</label>
                    <select name="department_id" id="department_id" class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- ທຸກພາກສ່ວນ --</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- ກັ່ນຕອງຕາມສະຖານະ -->
            @if(!empty($statuses))
                <div class="sm:col-span-1 md:col-span-1 lg:col-span-1">
                    <label for="status" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">ສະຖານະ</label>
                    <select name="status" id="status" class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- ທຸກສະຖານະ --</option>
                        @foreach ($statuses as $statusCode => $statusName)
                            <option value="{{ $statusCode }}" @selected(request('status') == $statusCode)>
                                {{ $statusName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- ກັ່ນຕອງຕາມວັນທີ -->
            <div class="sm:col-span-1 md:col-span-1 lg:col-span-1">
                <label for="date" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">ວັນທີສ້າງ</label>
                <input type="text" name="date" id="date" value="{{ request('date') }}" class="block w-full border-gray-300 rounded-md shadow-sm datepicker text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="ວ/ດ/ປ" x-data x-init="initDatepicker($el)"> 
            </div>

            <!-- ປຸ່ມກົດ (Responsive) -->
            <div class="sm:col-span-2 md:col-span-full lg:col-span-1 flex flex-col sm:flex-row gap-2 mt-2 lg:mt-0">
                <button type="submit" class="w-full sm:flex-1 inline-flex justify-center items-center px-4 py-2.5 lg:py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none transition shadow-sm">
                    ຄົ້ນຫາ
                </button>
                <a href="{{ $action }}" class="w-full sm:flex-1 inline-flex justify-center items-center px-4 py-2.5 lg:py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none transition text-center">
                    ລຶບລ້າງ
                </a>
            </div>

        </div>
    </form>
</div>