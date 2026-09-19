@props([
    'action', 
    'departments', 
    'statuses' => [],
    'titleSpan' => 'md:col-span-1' // ค่า Default
])
<div class="mb-6 border-b border-gray-200 pb-4">
    <form action="{{ $action }}" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
            <div class="md:col-span-1">
                <label for="doc_code" class="block text-sm font-medium text-gray-700">ຄົ້ນຫາຕາມລະຫັດ</label>
                <input type="text" name="doc_code" id="doc_code" value="{{ request('doc_code') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="ພີມລະຫັດ...">
            </div>

            <!-- Search by Title -->
            <div class="{{ $titleSpan }}">
                <label for="title" class="block text-sm font-medium text-gray-700">ຄົ້ນຫາຕາມຫົວຂໍ້</label>
                <input type="text" name="title" id="title" value="{{ request('title') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="ພີມຫົວຂໍ້...">
            </div>
            <!-- Filter by Department -->
            @if(isset($departments) && $departments->isNotEmpty())
            <div class="md:col-span-1">
                <label for="department_id" class="block text-sm font-medium text-gray-700">ຄົ້ນຫາຕາມພາກສ່ວນ</label>
                <select name="department_id" id="department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">-- ທຸກພາກສ່ວນ --</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                </select>
            </div>
            @endif
            <!-- Filter by Date -->
            <div class="md:col-span-1">
                <label for="date" class="block text-sm font-medium text-gray-700">ຄົ້ນຫາຕາມວັນທີ</label>
                    <input type="text" name="date" id="date" value="{{ request('date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm datepicker" placeholder="ວັນທີ/ເດືອນ/ປີ" x-data x-init="initDatepicker($el)"> 
                    <!--x-init="flatpickr($el, { dateFormat: 'd/m/Y', allowInput: true, locale: 'lo' })"-->
            </div>

            @if(!empty($statuses))
            <div>
                <label for="status" class="block text-sm ...">ຄົ້ນຫາຕາມສະຖານະ</label>
                <select name="status" id="status" class="mt-1 block w-full ...">
                    <option value="">-- ທຸກສະຖານະ --</option>
                    @foreach ($statuses as $statusCode => $statusName)
                        <option value="{{ $statusCode }}" @selected(request('status') == $statusCode)>
                            {{ $statusName }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Buttons -->
            <div class="md:col-span-1 flex items-end space-x-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md">ຄົ້ນຫາ</button>
                <a href="{{ $action }}" class="inline-flex items-center px-4 py-2 bg-gray-400 text-white rounded-md">ລຶບລ້າງ</a>
            </div>
        </div>
    </form>
</div>