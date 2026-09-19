<?php
namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\DocumentPaid;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use App\Models\Department; 

class CashDashboardController extends Controller
{
    use AuthorizesRequests;
    /**
     * ສະແດງ dashboard ຄັງເງິນສົດ.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        
        // 1. เริ่ม Query ที่สถานะ PENDING_SECRETARY_REVIEW เท่านั้น
        $query = Document::where('status', 'PENDING_SECRETARY_REVIEW');

        // 2. กรองตาม "รหัสเอกสาร"
        if ($request->filled('doc_code')) {
            $query->where('document_code', 'like', '%' . $request->doc_code . '%');
        }

        // 3. กรองตาม "หัวข้อเอกสาร" (Search)
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // 4. กรองตาม "ภาคส่วน" (Filter by Department)
        if ($request->filled('department_id')) {
            // เราต้อง Query ผ่าน Relationship 'requester'
            $query->whereHas('requester', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // 5. กรองตาม "วันที่"
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // ດຶງຂໍ້ມູນສະເພາະເອກະສານທີ່ລໍຖ້າການຈ່າຍເງິນຈາກຄັງເງິນສົດ
        $payableDocuments = Document::whereIn('status', [
                                        'PENDING_CASHIER_WITHDRAWAL_SLIP', 
                                        'READY_FOR_PAYMENT'               
                                    ])
                                    ->with('requester.department', 'documentType')
                                    ->latest()
                                    ->paginate(15);
        
        $departments = \App\Models\Department::orderBy('name')->get();

        return view('cashier.dashboard', compact('payableDocuments', 'departments'));
    }

    public function show(Document $document)
    {
        // ແກ້ໄຂບ່ອນນີ້: ໃຫ້ຍອມຮັບທັງ 2 ສະຖານະຂອງຄັງເງິນສົດ
        if (!in_array($document->status, ['PENDING_CASHIER_WITHDRAWAL_SLIP', 'READY_FOR_PAYMENT'])) {
            return redirect()->route('cashier.dashboard')->with('error', 'ເອກະສານນີ້ບໍ່ຢູ່ໃນສະຖານະທີ່ສາມາດດຳເນີນການໄດ້ໃນຂະນະນີ້.');
        }

        $document->load('documentType', 'documentItems', 'attachments', 'requester.department');

        return view('cashier.documents.show', compact('document'));
    }

    // ປ່ຽນຊື່ method ຈາກ confirmPayment ເປັນ process
    public function process(Request $request, Document $document)
    {
        // 1. ກວດສອບສະຖານະປັດຈຸບັນ (ຕ້ອງເປັນ "ລໍຖ້າຕີໃບຖອນ" ຫຼື "ພ້ອມຈ່າຍ")
        if (!in_array($document->status, ['PENDING_CASHIER_WITHDRAWAL_SLIP', 'READY_FOR_PAYMENT'])) {
            return redirect()->route('cashier.dashboard')->with('error', 'ເອກະສານນີ້ໄດ້ຖືກດຳເນີນການໄປແລ້ວ.');
        }

        $action = $request->input('action', 'approve'); // ຮັບຄ່າ 'approve' ຫຼື 'reject'
        $nextStatus = '';
        $logAction = '';
        $logComment = '';

        // --- ເລີ່ມ Transaction ---
        DB::beginTransaction();
        try {
            // 2. ເຮັດເຄື່ອງໝາຍວ່າອ່ານແລ້ວ
            Auth::user()->unreadNotifications
                ->where('data.document_id', $document->id)
                ->markAsRead();

            if ($action === 'approve') {
                // ແຍກ Logic ຕາມສະຖານະຂອງເອກະສານ
                if ($document->status === 'PENDING_CASHIER_WITHDRAWAL_SLIP') {
                    $nextStatus = 'PENDING_ACCOUNTANT_VERIFICATION'; // ສົ່ງຕໍ່ໃຫ້ ນາຍບັນຊີ ເຊັນຢັ້ງຢືນ
                    $logAction = 'Withdrawal Slip Created';
                    $logComment = 'ຄັງເງິນສົດຕີໃບຖອນສຳເລັດ, ສົ່ງຕໍ່ໃຫ້ນາຍບັນຊີເຊັນຢັ້ງຢືນ.';
                } elseif ($document->status === 'READY_FOR_PAYMENT') {
                    $nextStatus = 'PAID'; // ສຳເລັດຂະບວນການຈ່າຍເງິນ
                    $logAction = 'Payment Confirmed by Cashier';
                    $logComment = 'ດຳເນີນການຈ່າຍເງິນສົດສຳເລັດ.';
                }

                // 3. ອັບເດດສະຖານະເອກະສານ
                $document->status = $nextStatus;
                $document->save();

                // 4. ບັນທຶກປະຫວັດ (Log)
                $document->documentLogs()->create([
                    'user_id' => Auth::id(),
                    'action' => $logAction,
                    'comment' => $logComment
                ]);

                DB::commit();

            } else {
                // ຖ້າມີການປະຕິເສດ (Reject) ຈາກຄັງເງິນສົດ (ຖ້າຕ້ອງການໃຫ້ມີ)
                DB::rollBack();
                return back()->with('error', 'ການດຳເນີນການບໍ່ຖືກຕ້ອງ.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກຂໍ້ມູນ: ' . $e->getMessage());
        }

        // --- 5. ສ່ວນການສົ່ງ Notification (ຢູ່ນອກ Transaction) ---
        
        if ($nextStatus === 'PENDING_ACCOUNTANT_VERIFICATION') {
            // ກໍລະນີຕີໃບຖອນສຳເລັດ: ສົ່ງແຈ້ງເຕືອນໄປຫານາຍບັນຊີ (Accountant) ທຸກຄົນ
            $accountants = \App\Models\User::whereHas('role', function ($q) {
                $q->where('name', 'Accountant');
            })->get();

            foreach ($accountants as $accountant) {
                $accountant->notify(new \App\Notifications\DocumentSubmitted($document));
            }

        } elseif ($nextStatus === 'PAID') {
            // ກໍລະນີຈ່າຍເງິນສຳເລັດ: ສົ່ງແຈ້ງເຕືອນຫາ ຜູ້ສະເໜີ (Requester)
            $requester = $document->requester;
            if ($requester) {
                $requester->notify(new \App\Notifications\DocumentPaid($document));
            }

            // ຖ້າຜູ້ສະເໜີແມ່ນ ຝ່າຍຈັດຊື້ (Procurement_Staff), ໃຫ້ແຈ້ງເຕືອນຄົນໃນພະແນກຈັດຕັ້ງນຳ
            if ($requester && $requester->role->name === 'Procurement_Staff') {
                $orgDepartmentId = $document->department_id;

                $departmentStaff = \App\Models\User::where('department_id', $orgDepartmentId)
                    ->where('id', '!=', $requester->id)
                    ->whereHas('role', function ($q) { $q->where('name', 'Staff'); })
                    ->get();

                $notificationForDept = new \App\Notifications\DocumentPaid(
                    $document, 
                    "ເອກະສານຂອງພະແນກທ່ານ (ສ້າງໂດຍຝ່າຍຈັດຊື້) ໄດ້ຮັບການຈ່າຍເງິນແລ້ວ."
                );
                                               
                foreach ($departmentStaff as $staff) {
                    $staff->notify($notificationForDept);
                }
            }
        }

        // 6. Redirect ກັບຄືນໜ້າ Dashboard
        $successMessage = ($nextStatus === 'PAID') ? 'ຢືນຢັນການຈ່າຍເງິນສົດສຳເລັດແລ້ວ.' : 'ຢືນຢັນການຕີໃບຖອນ ແລະ ສົ່ງຕໍ່ສຳເລັດແລ້ວ.';
        return redirect()->route('cashier.dashboard')->with('success', $successMessage);
    }
    
    public function approvedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        // 1. ค้นหา ID ของเอกสารทั้งหมดที่เลขาคนนี้เคย "อนุมัติ"
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Payment Confirmed by Cashier')
                                          ->pluck('document_id');

        // 2. ดึงข้อมูลเอกสารเหล่านั้นมาแสดงผล, พร้อมกับฟังก์ชันค้นหา/กรอง
        $query = Document::whereIn('id', $documentIds);
    
        // ... (สามารถเพิ่ม Logic การค้นหาและกรองได้ที่นี่) ...
        // 2. กรองตาม "รหัสเอกสาร"
        if ($request->filled('doc_code')) {
            $query->where('document_code', 'like', '%' . $request->doc_code . '%');
        }

        // 3. กรองตาม "หัวข้อเอกสาร" (Search)
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // 4. กรองตาม "ภาคส่วน" (Filter by Department)
        if ($request->filled('department_id')) {
            // เราต้อง Query ผ่าน Relationship 'requester'
            $query->whereHas('requester', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // 5. กรองตาม "วันที่"
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
    
        // (ใหม่) กรองตามสถานะ
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->with('requester.department', 'documentType')
                       ->latest('updated_at')
                       ->paginate(15);
    
        // ดึงข้อมูลสำหรับ Dropdowns
        $departments = \App\Models\Department::orderBy('name')->get();
        $statuses = [
            // กำหนดสถานะที่เกี่ยวข้องกับประวัติ
            'PAID' => 'ຈ່າຍເງິນແລ້ວ',
            'REJECTED' => 'ຖືກປະຕິເສດ',
            'COMPLETED' => 'ສໍາເລັດສົມບຸນ',
        ];
        
        // เราจะสร้าง View นี้ต่อไป
        return view('cashier.history.approved', compact('documents', 'departments', 'statuses'));
    }

    /**
     * ສະແດງປະຫວັດເອກະສານທີ່ຄັງເງິນສົດເຄີຍຕີໃບຖອນແລ້ວ.
     */
    public function withdrawalSlipsHistory(Request $request)
    {
        // 1. ຄົ້ນຫາ ID ຂອງເອກະສານທັງໝົດທີ່ເຮົາເຄີຍຕີໃບຖອນ
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                              ->where('action', 'Withdrawal Slip Created')
                                              ->pluck('document_id');

        // 2. ດຶງຂໍ້ມູນເອກະສານເຫຼົ່ານັ້ນມາສະແດງຜົນ
        $documents = Document::whereIn('id', $documentIds)
                               ->with('requester.department', 'documentType')
                               ->latest('updated_at')
                               ->paginate(15);

        $departments = Department::orderBy('name')->get();
        //return view('cashier.history.withdrawal_slips', compact('documents'));
        return view('cashier.history.withdrawal_slips', compact('documents', 'departments'));
    }
}