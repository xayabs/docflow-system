<?php
namespace App\Http\Controllers\ViceDean;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Import User Model
use App\Notifications\DocumentSubmitted;
use App\Notifications\DocumentRejected;
use App\Notifications\DocumentWasRejectedToApprover;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\PrivateNote;
use Illuminate\Support\Facades\DB;

class VDDashboardController extends Controller
{
    use AuthorizesRequests;
    /**
     * ສະແດງ dashboard ຂອງຝ່າຍກວດສອບງົບປະມານ ແລະ ບັນຊີ.
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

        // ດຶງຂໍ້ມູນສະເພາະເອກະສານທີ່ລໍຖ້າການກວດສອບຈາກຝ່າຍກະກຽມເອກະສານ
        $pendingDocuments = Document::where('status', 'PENDING_VICE_DEAN_APPROVAL')
                                    ->with('requester.department', 'documentType')
                                    ->latest()
                                    ->paginate(10);

        $departments = \App\Models\Department::orderBy('name')->get();
        
        return view('vicedean.dashboard', compact('pendingDocuments', 'departments'));
    }

    public function show(Document $document)
    {
        // ດຶງຂໍ້ມູນທີ່ກ່ຽວຂ້ອງທັງໝົດມາພ້ອມກັນ
        $document->load('documentType', 'documentItems', 'attachments', 'requester.department', 'documentLogs.user.role');
        $privateNotes = PrivateNote::where('document_id', $document->id)
                               ->where('recipient_id', auth()->id())
                               ->with('sender') // ดึงข้อมูลผู้ส่ง
                               ->get();

        // ຕໍ່ໄປເຮົາຈະສ້າງ View ນີ້
        return view('vicedean.documents.show', compact('document', 'privateNotes'));
    }

    /**
    * Approve the document and move it to the next step in the workflow.
    */
    public function approve(Document $document)
    {
        // 1. ກວດສອບເພື່ອຄວາມແນ່ນອນວ່າເອກະສານຢູ່ໃນສະຖານະທີ່ຖືກຕ້ອງ
        if ($document->status !== 'PENDING_VICE_DEAN_APPROVAL') {
            return back()->with('error', 'ເອກະສານນີ້ບໍ່ໄດ້ຢູ່ໃນສະຖານະທີ່ລໍຖ້າການກວດສອບ.');
        }

        // ຄົ້ນຫາ ແລະ ອັບເດດການແຈ້ງເຕືອນທີ່ກ່ຽວຂ້ອງກັບເອກະສານນີ້ ໃຫ້ເປັນ "ອ່ານແລ້ວ"
        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();

        // 2. ປ່ຽນສະຖານະຂອງເອກະສານໄປຂັ້ນຕອນຕໍ່ໄປ
        //$document->status = 'PENDING_FINANCE_HEAD_APPROVAL';
        $document->status = 'PENDING_ACCOUNTANT_POSTING'; 
        $document->save();

        // 3. ບັນທຶກປະຫວັດການດຳເນີນການ (Log)
        $document->documentLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'Approved by Vice Dean',
            'comment' => 'ອະນຸມັດແລ້ວ, ສົ່ງກັບໄປໃຫ້ນາຍບັນຊີເພື່ອລົງບັນຊີ.'
        ]);
        
        // ຄົ້ນຫາຜູ້ໃຊ້ທຸກຄົນທີ່ມີ Role ເປັນ Head_of_Finance
        $accountants = User::whereHas('role', function ($query) {
            $query->where('name', 'Accountant');
        })->get();

        // ສົ່ງ Notification ໄປໃຫ້ Vice Dean ທຸກຄົນ
        // ເຮົາສາມາດສ້າງ Notification Class ໃໝ່ ຫຼືປັບປຸງຂອງເດີມໃຫ້ຢຶດຢຸ່ນຂື້ນ
        // ເພື່ອຄວາມງ່າຍ, ເຮົາຈະໃຊ້ DocumentSubmitted ໄປກ່ອນ
        foreach ($accountants as $accountant) {
            // ເຮົາຄວນສ້າງ Notification ໃໝ່ທີ່ຂໍ້ຄວາມເໝາະສົມກວ່າ
            // ເຊັ່ນ new DocumentForwarded($document)
            $accountant->notify(new DocumentSubmitted($document));
        }

        // 4. ສົ່ງກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມແຈ້ງເຕືອນ
        return redirect()->route('vicedean.dashboard')->with('success', 'ອະນຸມັດແລະສົ່ງກັບໄປໃຫ້ນາຍບັນຊີຮຽບຮ້ອຍແລ້ວ.');
    }

    /**
    * Reject the document and send it back to the requester.
    */
    public function reject(Request $request, Document $document)
    {
        DB::beginTransaction(); // <-- เริ่ม Transaction
        Try {
            // --- ย้าย Logic ทั้งหมดเข้ามาใน try block ---
        
            $request->validate(['rejection_reason' => 'required|string|min:10']);
    
            if ($document->status !== 'PENDING_VICE_DEAN_APPROVAL') {
                throw new \Exception('ເອກະສານນີ້ບໍ່ໄດ້ຢູ່ໃນສະຖານະທີ່ລໍຖ້າການກວດສອບ.');
            }

            Auth::user()->unreadNotifications->where('data.document_id', $document->id)->markAsRead();

            $document->status_before_rejected = $document->status;
        
            $document->status = 'REJECTED';
            $document->rejected_reason = $request->input('rejection_reason');
            $document->save();
    
            $document->documentLogs()->create([
                'user_id' => Auth::id(),
                'action' => 'Rejected by Vice Dean',
                'comment' => $request->input('rejection_reason')
            ]);

            // ถ้าทุกอย่างสำเร็จ, ยืนยันการเปลี่ยนแปลง
            DB::commit(); // <-- ยืนยัน

        } catch (\Exception $e) {
            DB::rollBack(); // <-- ยกเลิกถ้าเกิด Error
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດໃນການປະຕິເສດເອກະສານ: ' . $e->getMessage())->withInput();
        }

        // --- ส่วนการส่ง Notification จะอยู่นอก Transaction ---
        $requester = $document->requester;
        if ($requester) {
            $requester->notify(new \App\Notifications\DocumentRejected($document, auth()->user()));
        }

        $concernedRoles = ['Dean_Secretary', 'Accountant', 'Finance_Preparer'];
        $recipients = \App\Models\User::whereHas('role', function ($query) use ($concernedRoles) {
            $query->whereIn('name', $concernedRoles);
        })->get();
    
        foreach ($recipients as $recipient) {
            $recipient->notify(new \App\Notifications\DocumentWasRejectedToApprover($document, auth()->user()));
        }

        if ($requester && $requester->role->name === 'Procurement_Staff') {
        
            $orgDepartmentId = $requester->department_id; // ID ของแผนกจัดตั้ง

            // ค้นหา Staff ทุกคนในแผนกนั้น (ยกเว้นตัว Procurement_Staff เอง)
            $departmentStaff = \App\Models\User::where('department_id', $orgDepartmentId)
                                           ->where('id', '!=', $requester->id)
                                           ->whereHas('role', function ($q) { $q->where('name', 'Staff'); })
                                           ->get();
        
            // (แนะนำให้สร้าง Notification ใหม่)
            $notificationForDept = new \App\Notifications\DocumentWasRejectedToApprover(
                $document, 
                auth()->user(), 
                "ເອກະສານທີ່ສ້າງໂດຍຝ່ານຈັດຊື້ໃນພະແນກຂອງທ່ານທີ່ຖືກປະຕິເສດ"
            );
                                           
            foreach ($departmentStaff as $staff) {
                $staff->notify($notificationForDept);
            }
        }

        return redirect()->route('vicedean.dashboard')->with('success', 'ການສົ່ງເອກະສານກັບສຳເລັດແລ້ວ.');
    }
    /*
    public function reject(Request $request, Document $document)
    {
        // 1. ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນທີ່ສົ່ງມາ (ເຫດຜົນ)
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);
    
        // 2. ກວດສອບສະຖານະເອກະສານ
        if ($document->status !== 'PENDING_VICE_DEAN_APPROVAL') {
            return back()->with('error', 'ເອກະສານນີ້ບໍ່ໄດ້ຢູ່ໃນສະຖານະທີ່ລໍຖ້າການກວດສອບ.');
        }

        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();

        // 3. บันทึกสถานะปัจจุบัน (ก่อนที่จะเปลี่ยนเป็น REJECTED)
        $document->status_before_rejected = $document->status;
        
        // 4. ປ່ຽນສະຖານະເອກະສານເປັນ REJECTED ແລະ ບັນທຶກເຫດຜົນ
        $document->status = 'REJECTED';
        $document->rejected_reason = $request->input('rejection_reason');
        $document->save();
    
        // 5. ບັນທຶກປະຫວັດການດຳເນີນການ (Log)
        $document->documentLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'Rejected by Vice Dean',
            'comment' => $request->input('rejection_reason')
        ]);

        // 6. แจ้งเตือนผู้สร้าง (Requester) - เหมือนเดิม
        $requester = $document->requester;
        if ($requester) {
            // (แนะนำให้สร้าง Notification Class ใหม่: DocumentRejected)
            $requester->notify(new \App\Notifications\DocumentRejected($document, auth()->user()));
        }

        // 7. ค้นหาผู้ใช้ใน Role ที่เกี่ยวข้อง
        $concernedRoles = ['Dean_Secretary','Accountant', 'Finance_Preparer'];
        $recipients = \App\Models\User::whereHas('role', function ($query) use ($concernedRoles) {
            $query->whereIn('name', $concernedRoles);
        })->get();

        // 8. ส่ง Notification ไปให้ผู้รับทุกคน
        foreach ($recipients as $recipient) {
            $recipient->notify(new DocumentWasRejectedToApprover($document, auth()->user()));
        }

        // 9. ສົ່ງກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມແຈ້ງເຕືອນ
        return redirect()->route('vicedean.dashboard')->with('success', 'ປະຕິເສດເອກະສານສຳເລັດແລ້ວ.');
    }*/

    public function approvedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        // 1. ค้นหา ID ของเอกสารทั้งหมดที่เลขาคนนี้เคย "อนุมัติ"
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Approved by Vice Dean')
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
        return view('vicedean.history.approved', compact('documents', 'departments', 'statuses'));
    }

    /**
    * แสดงประวัติเอกสารที่ปฏิเสธโดยเลขาคนปัจจุบัน.
    */
    public function rejectedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Rejected by Vice Dean')
                                          ->pluck('document_id');

        $query = Document::whereIn('id', $documentIds);

        // ===== เพิ่ม Logic การกรองข้อมูล =====
        if ($request->filled('doc_code')) {
            $query->where('document_code', 'like', '%' . $request->doc_code . '%');
        }
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->filled('department_id')) {
            $query->whereHas('requester', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
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
        $departments = \App\Models\Department::orderBy('name')->get();
        $statuses = [ // เราสามารถกำหนดสถานะที่เกี่ยวข้องกับหน้านี้ได้
                    'REJECTED' => 'ຖືກປະຕິເສດ',
                ];

        return view('vicedean.history.rejected', compact('documents', 'departments', 'statuses'));
    }
}