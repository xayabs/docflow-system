<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Department;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // <-- Import Excel Facade
use App\Exports\DocumentsExport; // <-- Import Export Class ที่เราจะสร้างต่อไป

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // ເລີ່ມຕົ້ນ Query
        $query = Document::query()->with('requester', 'department', 'documentType');

        // ກັ້ນຕອງຕາມວັນທີ
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        // ກັ່ນຕອງຕາມພາກວິຊາ/ພະແນກ
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        // ກັ່ນຕອງຕາມສະຖານະ
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ດືງຂໍືມູນທີ່ກອງແລ້ວມາສະແດງ
        $documents = $query->latest()->paginate(20)->withQueryString();
        
        // ດືງຂໍ້ມູນສໍາຫຼັບ Dropdown
        $departments = Department::orderBy('name')->get();
        // ດືງຄ່າສະຖານະພາບທັງມົດຈາກ helper ຫຼື ກໍານົດເອງ
        $statuses = [
            'PAID' => 'ຈ່າຍເງິນແລ້ວ',
            'REJECTED' => 'ປະຕິເສດ',
            'COMPLETED' => 'ສໍາເລັດສົມບູນ',
            // ສາມາດເພີ່ມສະຖານະອື່ນໆ ທີ່ຕ້ອງການກອງໄດ້
        ];

        return view('reports.documents', compact('documents', 'departments', 'statuses'));
    }

    public function export(Request $request)
    {
        // ຮັບຄ່າ filter ຈາກ request ມາສ້າງຊື່ຟາຍ
        $fileName = 'document_report_' . now()->format('Y-m-d') . '.xlsx';
        
        // ເອີ້ນໃຊ້ Export Class ພ້ອມກັບສົ່ງຄ່າ filter ໄປພ້ອມ
        return Excel::download(new DocumentsExport($request->all()), $fileName);
    }
}
