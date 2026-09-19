<?php

namespace App\Exports;

use App\Models\Document;
use App\Models\DocumentLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SecretaryApprovedHistoryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $userId;

    // ຮັບຄ່າ User ID ຂອງເລຂາທີ່ກຳລັງ Export ຂໍ້ມູນ
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // 1. ຄົ້ນຫາ ID ຂອງເອກະສານທັງໝົດທີ່ເລຂາຄົນນີ້ເຄີຍ "ອະນຸມັດ"
        $documentIds = DocumentLog::where('user_id', $this->userId)
                                  ->where('action', 'Approved by Secretary')
                                  ->pluck('document_id');

        // 2. ດຶງຂໍ້ມູນເອກະສານເຫຼົ່ານັ້ນມາເພື່ອ Export
        return Document::whereIn('id', $documentIds)
                       ->with('requester.department', 'documentType')
                       ->latest('updated_at')
                       ->get();
    }

    /**
     * ກຳນົດຫົວຂໍ້ຄໍລຳ (Headings) ໃນ Excel.
     */
    public function headings(): array
    {
        return [
            'ລຳດັບ',
            'ລະຫັດເອກະສານ',
            'ຫົວຂໍ້ເອກະສານ',
            'ປະເພດເອກະສານ',
            'ພາກສ່ວນສະເໜີ',
            'ຜູ້ສະເໜີ',
            'ມູນຄ່າລວມ (ກີບ)',
            'ສະຖານະປະຈຸບັນ',
            'ວັນທີສົ່ງ',
        ];
    }

    /**
     * ຈັດຮູບແບບຂໍ້ມູນແຕ່ລະແຖວ (Rows) ກ່ອນລົງ Excel.
     */
    public function map($document): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $document->document_code ?? '-',
            $document->title,
            $document->documentType->name ?? 'N/A',
            $document->requester->department->name ?? 'N/A',
            $document->requester->name ?? 'N/A',
            $document->total_amount, // ສາມາດໃສ່ number_format() ໄດ້ຖ້າຕ້ອງການ
            translateStatus($document->status), // ໃຊ້ Helper Function ຂອງເຮົາ
            $document->created_at->format('d/m/Y H:i'),
        ];
    }
}
