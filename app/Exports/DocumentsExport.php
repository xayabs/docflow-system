<?php

namespace App\Exports;

use App\Models\Document;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // ເພີ່ມນີ້ເພື່ອໃຫ້ຂະໜາດ column ພໍດີອັດຕະໂນມັດ

class DocumentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Document::query()->with('requester.department', 'documentType');

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('documents.created_at', [$this->filters['start_date'], $this->filters['end_date'] . ' 23:59:59']);
        }
        if (!empty($this->filters['department_id'])) {
            $query->where('department_id', $this->filters['department_id']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->latest('documents.created_at')->get();
    }

    /**
     * ກຳນົດຫົວຂໍ້ column.
     */
    public function headings(): array
    {
        return [
            'ID',
            'ຫົວຂໍ້',
            'ປະເພດ',
            'ຜູ້ຮ້ອງຂໍ',
            'ພາກສ່ວນ',
            'ມູນຄ່າລວມ',
            'ສະຖານະ',
            'ວັນທີສ້າງ',
        ];
    }

    /**
     * ແປງຂໍ້ມູນແຕ່ລະແຖວ.
     */
    public function map($document): array
    {
        return [
            $document->id,
            $document->title,
            $document->documentType->name ?? '',
            $document->requester->name ?? '',
            $document->requester->department->name ?? '', // ປ່ຽນມາໃຊ້ requester->department
            $document->total_amount,
            translateStatus($document->status), // ໃຊ້ helper function ຂອງເຮົາ
            $document->created_at->format('Y-m-d H:i:s'),
        ];
    }
}