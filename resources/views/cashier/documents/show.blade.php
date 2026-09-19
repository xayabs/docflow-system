<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">
            {{ __('ກວດສອບເອກະສານ:') }} {{ $document->title }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg rounded-xl border border-gray-200">
                <div class="p-4 sm:p-6 text-gray-900 space-y-6 md:space-y-8">
                    
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
                            <strong class="font-bold">ເກີດຂໍ້ຜິດພາດ!</strong>
                            <ul class="mt-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- Section 1: Document Summary (Responsive)                    --}}
                    {{-- ========================================================== --}}
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <h3 class="text-base md:text-lg font-bold border-b border-gray-200 pb-2 mb-4 text-gray-800">ສະຫຼຸບຂໍ້ມູນເອກະສານ</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ສະຖານະ</dt>
                                <dd class="text-sm">
                                    <span class="px-2.5 py-1 font-semibold rounded-full {{ getStatusColorClass($document->status) }}">
                                        {{ translateStatus($document->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ປະເພດ</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ $document->documentType->name ?? 'N/A' }}</dd>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ຜູ້ຮ້ອງຂໍ</dt>
                                <dd class="text-sm text-gray-900 font-medium">{{ $document->requester->name ?? 'N/A' }}</dd>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">ມູນຄ່າລວມ</dt>
                                <dd class="text-lg text-blue-600 font-bold">{{ number_format($document->total_amount, 0) }} KIP</dd>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================================== --}}
                    {{-- Section 2: Document Items (Responsive)                      --}}
                    {{-- ========================================================== --}}
                    <div>
                        <h3 class="text-base md:text-lg font-bold border-b border-gray-200 pb-2 mb-4 text-gray-800">ລາຍການເບີກຈ່າຍ</h3>
                        
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ລາຍລະອຽດ</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">ຈຳນວນ</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">ລາຄາຕໍ່ໜ່ວຍ</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">ລາຄາລວມ</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($document->documentItems as $item)
                                        <tr>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item->item_description }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($item->quantity, 0) }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($item->unit_price, 0) }}</td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">{{ number_format($item->total_price, 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">ບໍ່ມີລາຍການເບີກຈ່າຍ</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="block md:hidden space-y-3">
                            @forelse ($document->documentItems as $item)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-sm">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ $item->item_description }}</h4>
                                    <div class="flex justify-between text-gray-600">
                                        <span>ຈຳນວນ: {{ number_format($item->quantity, 0) }}</span>
                                        <span>ລາຄາ: {{ number_format($item->unit_price, 0) }}</span>
                                    </div>
                                    <div class="mt-2 text-right font-bold text-blue-600">ລວມ: {{ number_format($item->total_price, 0) }}</div>
                                </div>
                            @empty
                                <div class="text-center py-4 bg-gray-50 rounded-lg border text-sm text-gray-500">ບໍ່ມີລາຍການເບີກຈ່າຍ</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Section 3: History & Action Buttons --}}
                    <x-document-history :logs="$document->documentLogs" />
                        
                    @if(in_array($document->status, ['PENDING_CASHIER_WITHDRAWAL_SLIP', 'READY_FOR_PAYMENT']))
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-base md:text-lg font-bold mb-4 text-gray-800">ການດຳເນີນການ (ຄັງເງິນ)</h3>
                            
                            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                                <a href="{{ route('cashier.dashboard') }}" class="w-full sm:w-auto text-center px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 transition">
                                    ກັບຄືນ
                                </a>
                                <form action="{{ route('cashier.documents.process', $document->id) }}" method="POST" class="w-full sm:w-auto"
                                      onsubmit="return confirm('{{ $document->status === 'PENDING_CASHIER_WITHDRAWAL_SLIP' ? 'ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການຢືນຢັນການຕີໃບຖອນເງິນນີ້?' : 'ທ່ານແນ່ໃຈບໍ່ວ່າໄດ້ດໍາເນີນການຈ່າຍເງິນສົດສໍາລັບເອກະສານນີ້ແລ້ວ?' }}')">
                                    @csrf
                                    <button type="submit" name="action" value="approve" class="inline-flex items-center px-6 py-2.5 bg-green-600 text-white rounded-lg font-bold text-sm hover:bg-green-700 shadow-md transition">
                                        {{ $document->status === 'PENDING_CASHIER_WITHDRAWAL_SLIP' ? 'ຢືນຢັນການຕີໃບຖອນ' : 'ຢືນຢັນການຈ່າຍເງິນ' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="mt-8 pt-6 border-t text-right">
                            <a href="{{ url()->previous() }}" class="inline-flex items-center px-6 py-2.5 bg-gray-100 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-200 transition">
                                &larr; ກັບຄືນ
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>