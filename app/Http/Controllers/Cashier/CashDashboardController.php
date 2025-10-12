<?php
namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\DocumentPaid;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

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
        $payableDocuments = Document::where('status', 'READY_FOR_PAYMENT')
                                    ->with('requester.department', 'documentType')
                                    ->latest()
                                    ->paginate(15);

        $departments = \App\Models\Department::orderBy('name')->get();

        return view('cashier.dashboard', compact('payableDocuments', 'departments'));
    }

    public function show(Document $document)
    {
        // ກວດສອບສະຖານະກ່ອນ
        if ($document->status !== 'READY_FOR_PAYMENT') {
            return redirect()->route('cashier.dashboard')->with('error', 'ເອກະສານນີ້ບໍ່ຢູ່ໃນສະຖານະທີ່ລໍຖ້າຈ່າຍເງິນ');
        }

        $document->load('documentType', 'documentItems', 'attachments', 'requester.department', 'documentLogs.user.role');

        return view('cashier.documents.show', compact('document'));
    }
    
    public function confirmPayment(Document $document)
    {
        // 1. ตรวจสอบสถานะ (ถูกต้อง)
        if ($document->status !== 'READY_FOR_PAYMENT') {
            return redirect()->route('cashier.dashboard')->with('error', 'ເອກະສານນີ້ໄດ້ຖືກດຳເນີນການໄປແລ້ວ');
        }

        // --- เริ่ม Transaction ---
        DB::beginTransaction();
        try {
            // 2. Mark as Read (ถูกต้อง)
            Auth::user()->unreadNotifications
                ->where('data.document_id', $document->id)
                ->markAsRead();
            
            // 3. เปลี่ยนสถานะ (ถูกต้อง)
            $document->status = 'PAID';
            $document->save();

            // 4. บันทึก Log (ถูกต้อง)
            $document->documentLogs()->create([
                'user_id' => Auth::id(),
                'action' => 'Payment Confirmed by Cashier',
                'comment' => 'ດຳເນີນການຈ່າຍເງິນສຳເລັດ.'
            ]);
        
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກຂໍ້ມູນ: ' . $e->getMessage());
        }

        // --- 5. ส่วนการส่ง Notification (อยู่นอก Transaction) ---
        // 5.1 ดึงข้อมูลผู้สร้าง
        $requester = $document->requester;

        // 5.2 ส่ง Notification ไปหาผู้สร้าง (Requester) เสมอ
        if ($requester) {
            $requester->notify(new \App\Notifications\DocumentPaid($document));
        }
    
        // 5.3 ถ้าผู้สร้างคือ Procurement_Staff, ให้แจ้งเตือนคนในแผนกจัดตั้งด้วย
        if ($requester && $requester->role->name === 'Procurement_Staff') {
        
            $orgDepartmentId = $document->department_id; // ID ของแผนกจัดตั้ง

            // ค้นหา Staff ทุกคนในแผนกนั้น (ยกเว้นตัว Procurement_Staff เอง)
            $departmentStaff = \App\Models\User::where('department_id', $orgDepartmentId)
                    ->where('id', '!=', $requester->id)
                    ->whereHas('role', function ($q) { $q->where('name', 'Staff'); })
                    ->get();
        
            // (เราต้องไปปรับปรุง DocumentPaid Notification ให้รับ Custom Message)
            $notificationForDept = new \App\Notifications\DocumentPaid(
                $document, 
                "ເອກະສານຂອງພະແນກທ່ານ(ສ້າງໂດຍຝ່າຍຈັດຊື້) ໄດ້ຮັບການຈ່າຍເງິນແລ້ວ"
            );
                                           
            foreach ($departmentStaff as $staff) {
                $staff->notify($notificationForDept);
            }
        }
        // --- จบส่วน Notification ---
        // 6. Redirect (ถูกต้อง)
        return redirect()->route('cashier.dashboard')->with('success', 'ຢືນຢັນການຈ່າຍເງິນສຳເລັດແລ້ວ.');
    }
    /*
    public function confirmPayment(Document $document)
    {
        // 1. ກວດສອບສະຖານະປັດຈຸບັນ
        if ($document->status !== 'READY_FOR_PAYMENT') {
            return redirect()->route('cashier.dashboard')->with('error', 'ເອກະສານນີ້ໄດ້ຖືກດຳເນີນການໄປແລ້ວ');
        }

        // ຄົ້ນຫາ ແລະ ອັບເດດການແຈ້ງເຕືອນທີ່ກ່ຽວຂ້ອງກັບເອກະສານນີ້ ໃຫ້ເປັນ "ອ່ານແລ້ວ"
        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();
            
        // 2. ປ່ຽນສະຖານະເປັນ "ຈ່າຍແລ້ວ"
        $document->status = 'PAID';
        $document->save();

        // 3. ບັນທຶກປະຫວັດ (Log)
        $document->documentLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'Payment Confirmed by Cashier',
            'comment' => 'ດຳເນີນການຈ່າຍເງິນສຳເລັດ.'
        ]);

        $requester = $document->requester;
        if ($requester) {
            $requester->notify(new DocumentPaid($document));
        }

        // 4. ສົ່ງກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມສຳເລັດ
        return redirect()->route('cashier.dashboard')->with('success', 'ຢືນຢັນການຈ່າຍເງິນສຳເລັດແລ້ວ.');
    }
    */
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
}