<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ເອກະສານລໍຖ້າຈ່າຍເງິນ:') }} {{ $document->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <strong class="font-bold">ເກີດຂໍ້ຜິດພາດ!</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Section 1: Document Summary (ຄືເກົ່າ) --}}
                    <div>
                        <h3 class="text-lg font-medium border-b pb-2 mb-4">ສະຫຼຸບຂໍ້ມູນເອກະສານ</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <dt class="text-base font-medium text-gray-500">ສະຖານະ</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full {{ getStatusColorClass($document->status) }}">
                                {{ translateStatus($document->status) }}</dd>
                                <!--inline-block">{{ $document->status }}-->
                            </div>
                            <div>
                                <dt class="text-base font-medium text-gray-500">ປະເພດ</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $document->documentType->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-base font-medium text-gray-500">ຜູ້ຮ້ອງຂໍ</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $document->requester->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-base font-medium text-gray-500">ພາກສ່ວນ</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $document->requester->department->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-base font-medium text-gray-500">ມູນຄ່າລວມ</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold">{{ number_format($document->total_amount, 2) }} KIP</dd>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Document Items (ຄືເກົ່າ) --}}
                    <div>
                        <h3 class="text-lg font-medium border-b pb-2 mb-4">ລາຍການເບີກຈ່າຍ</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-base font-bold text-gray-500 uppercase">ລາຍລະອຽດ</th>
                                    <th class="px-6 py-3 text-right text-base font-bold text-gray-500 uppercase">ຈຳນວນ</th>
                                    <th class="px-6 py-3 text-right text-base font-bold text-gray-500 uppercase">ລາຄາຕໍ່ໜ່ວຍ</th>
                                    <th class="px-6 py-3 text-right text-base font-bold text-gray-500 uppercase">ລາຄາລວມ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($document->documentItems as $item)
                                <tr>
                                    <td class="px-6 py-4">{{ $item->item_description }}</td>
                                    <td class="px-6 py-4 text-right">{{ number_format($item->quantity, 0) }}</td>
                                    <td class="px-6 py-4 text-right">{{ number_format($item->unit_price, 0) }}</td>
                                    <td class="px-6 py-4 text-right">{{ number_format($item->total_price, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        ບໍ່ມີລາຍການເບີກຈ່າຍ
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                            {{-- แสดงยอดรวมก็ต่อเมื่อมีรายการ --}}
                            @if($document->documentItems->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right font-bold">ລວມທັງໝົດ:</td>
                                        <td class="px-6 py-4 text-right font-bold">{{ number_format($document->total_amount, 0) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    {{-- Section 3: Attachments (ຄືເກົ່າ) --}}
                    <div>
                        <h3 class="text-lg font-medium border-b pb-2 mb-4">ໄຟລ໌ແນບ</h3>
                        <ul class="list-disc pl-5">
                            @forelse ($document->attachments as $attachment)
                                <li>
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="text-blue-600 hover:underline">
                                        {{ $attachment->file_name }}
                                    </a>
                                </li>
                            @empty
                                <li>ບໍ່ມີໄຟລ໌ແນບ</li>
                            @endforelse
                        </ul>
                    </div>
                    
                    {{-- Section: Document History --}}
                    <x-document-history :logs="$document->documentLogs" />
                        
                    {{-- =================================================== --}}
                    {{-- ===== Section 4: Action Buttons (ສ່ວນທີ່ເພີ່ມໃໝ່) ===== --}}
                    {{-- =================================================== --}}
                    {{-- ປ່ຽນແທນສ່ວນ Action Buttons ໃນ show.blade.php ຂອງຄັງເງິນສົດ --}}
                    @if(in_array($document->status, ['PENDING_CASHIER_WITHDRAWAL_SLIP', 'READY_FOR_PAYMENT']))
                        <div class="mt-6 pt-4 border-t">
                            <h3 class="text-lg font-medium mb-4">ການດຳເນີນການ</h3>
                            <div class="flex items-center justify-end space-x-4">
                                
                                {{-- ປຸ່ມກັບຄືນ --}}
                                <a href="{{ route('cashier.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                    ກັບຄືນ
                                </a>

                                {{-- ຟອມຢືນຢັນການເຮັດວຽກ --}}
                                <form action="{{ route('cashier.documents.process', $document->id) }}" method="POST" 
                                      onsubmit="return confirm('{{ $document->status === 'PENDING_CASHIER_WITHDRAWAL_SLIP' ? 'ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການຢືນຢັນການຕີໃບຖອນເງິນນີ້?' : 'ທ່ານແນ່ໃຈບໍ່ວ່າໄດ້ດຳເນີນການຈ່າຍເງິນສົດສຳລັບເອກະສານນີ້ແລ້ວ?' }}')">
                                    @csrf
                                    <button type="submit" name="action" value="approve" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                        {{ $document->status === 'PENDING_CASHIER_WITHDRAWAL_SLIP' ? 'ຢືນຢັນການຕີໃບຖອນ' : 'ຢືນຢັນວ່າຈ່າຍເງິນແລ້ວ' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- ປຸ່ມກັບຄືນ ຖ້າເອກະສານກວດຜ່ານໄປແລ້ວ --}}
                        <div class="mt-6 pt-4 border-t text-right">
                            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                ກັບຄືນ
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

