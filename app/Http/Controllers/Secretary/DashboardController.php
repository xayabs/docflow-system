<?php
namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Document; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Import User Model
use App\Notifications\DocumentSubmitted;
use App\Notifications\DocumentRejected;
use App\Notifications\DocumentForwarded;
use App\Models\Department;  
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\PrivateNote;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use AuthorizesRequests;
    /**
     * ສະແດງ dashboard ຂອງເລຂາ.
     *
     * @return \Illuminate\View\View
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

        // 6. ดึงข้อมูลที่กรองแล้วมาแสดงผล
        $pendingDocuments = $query->with('requester.department', 'documentType')
                                ->latest()
                                ->paginate(15)
                                ->withQueryString(); // สำคัญมาก

        // ດຶງຂໍ້ມູນສະເພາະເອກະສານທີ່ລໍຖ້າການກວດສອບຈາກເລຂາ
                                /*
        $pendingDocuments = Document::where('status', 'PENDING_SECRETARY_REVIEW')
                                    ->with('requester.department', 'documentType') // ໂຫຼດຂໍ້ມູນທີ່ກ່ຽວຂ້ອງມານຳ
                                    ->latest()
                                    ->paginate(10);*/
        // 7. ดึงข้อมูลภาคส่วนทั้งหมดสำหรับสร้าง Dropdown
        //$departments = Department::orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();

        // 8. ส่งข้อมูลทั้งหมดไปยัง View
        return view('secretary.dashboard', compact('pendingDocuments', 'departments'));
        //return view('secretary.dashboard', compact('pendingDocuments'));
    }

    public function show(Document $document)
    {
        // ດຶງຂໍ້ມູນທີ່ກ່ຽວຂ້ອງທັງໝົດມາພ້ອມກັນ
        $document->load('documentType', 'documentItems', 'attachments', 'requester.department', 'documentLogs.user.role');

        $recipientHeadFinance = \App\Models\User::whereHas('role', function ($q) { $q->where('name', 'Head_of_Finance'); })->first();
        $recipientViceDean = \App\Models\User::whereHas('role', function ($q) { $q->where('name', 'Vice_Dean'); })->first();
        $recipientDean = \App\Models\User::whereHas('role', function ($q) { $q->where('name', 'Dean'); })->first();

        // ຕໍ່ໄປເຮົາຈະສ້າງ View ນີ້
        return view('secretary.documents.show', compact(
            'document',
            'recipientViceDean',
            'recipientDean'
        ));
    }

    public function process(Request $request, Document $document)
    {
        // 1. ตรวจสอบสถานะก่อน
        if ($document->status !== 'PENDING_SECRETARY_REVIEW') {
            return back()->with('error', 'ເອກະສານນີ້ບໍ່ໄດ້ຢູ່ໃນສະຖານະທີ່ລໍຖ້າການກວດສອບ.');
        }

        $action = $request->input('action'); // 'approve' or 'reject'

        // --- 2. การ Validate แบบมีเงื่อนไข ---
        if ($action === 'reject') {
            $request->validate([
                'rejection_reason' => 'required|string|min:10',
            ]);
        }

        // (เราไม่จำเป็นต้อง Validate อะไรเลยในกรณี approve, นอกจากโน้ต)
        if ($request->filled('private_note') && $request->filled('recipient_ids')) {
            $request->validate([
                'recipient_ids'   => 'required|array',
                'recipient_ids.*' => 'exists:users,id',
                'private_note'    => 'required|string|min:5',
            ]);
        }
        
        DB::beginTransaction();
        try {
            // --- จัดการโน้ตส่วนตัว (ถ้ามี) ---
            if ($request->has('notes') && is_array($request->notes)) {
                foreach ($request->notes as $noteData) {
                    // ตรวจสอบว่ามีทั้ง "ผู้รับ" และ "ข้อความ"
                    if (!empty($noteData['recipient_ids']) && is_array($noteData['recipient_ids']) && !empty($noteData['message'])) {
                    
                        $recipients = \App\Models\User::find($noteData['recipient_ids']);
                        $noteContent = $noteData['message'];

                        foreach ($recipients as $recipient) {
                            \App\Models\PrivateNote::create([
                                'document_id' => $document->id,
                                'sender_id' => auth()->id(),
                                'recipient_id' => $recipient->id,
                                'note' => $noteContent,
                            ]);

                            $recipient->notify(new \App\Notifications\PrivateNoteReceived($document, auth()->user(), $noteContent));
                        }
                    }
                }
            }
            //dd('บันทึกและส่งโน้ตสำเร็จแล้ว!'); 
            // --- จัดการการ Approve/Reject (เหมือนเดิม) ---
            if ($action === 'approve') {
                // ຄົ້ນຫາ ແລະ ອັບເດດການແຈ້ງເຕືອນທີ່ກ່ຽວຂ້ອງກັບເອກະສານນີ້ ໃຫ້ເປັນ "ອ່ານແລ້ວ"
                Auth::user()->unreadNotifications
                    ->where('data.document_id', $document->id)
                    ->markAsRead();

                // 3. ກຳນົດຄ່າເລີ່ມຕົ້ນ
                $nextStatus = '';
                $recipients = null;
                $logComment = 'ເອກະສານຖືກຕ້ອງຕາມຮູບແບບ, ສົ່ງຕໍ່ໄປຂັ້ນຕອນຕໍ່ໄປ.';

                // 4. ແຍກສາຍທາງ ແລະ ກຳນົດຜູ້ຮັບ
                if ($document->document_type_id == 1) { // ຖ້າເປັນ "ຂໍຖອນເງິນ"
                    $nextStatus = 'PENDING_FINANCE_PREPARER_REVIEW';
                    $recipients = User::whereHas('role', function ($q) { $q->where('name', 'Finance_Preparer'); })->get();
    
                } elseif ($document->document_type_id == 2) { // ຖ້າເປັນ "ຂໍຈັດຊື້"
                    $nextStatus = 'PENDING_DEAN_APPROVAL';
                    // ຊອກຫາ Dean (ຫຼື Vice Dean, ແລ້ວແຕ່ Workflow ຂອງທ່ານ)
                    $recipients = User::whereHas('role', function ($q) { $q->where('name', 'Dean'); })->get();
                }

                // 5. ອັບເດດສະຖານະເອກະສານ
                $document->status = $nextStatus;
                $document->save();

                // 3. ບັນທຶກປະຫວັດການດຳເນີນການ (Log)
                $document->documentLogs()->create([
                    'user_id' => Auth::id(),
                    'action' => 'Approved by Secretary',
                    'comment' => $logComment
                ]);
        
                // ຄົ້ນຫາຜູ້ໃຊ້ທຸກຄົນທີ່ມີ Role ເປັນ Finance_Preparer/Dean
                // 7. ສົ່ງ Notification ໄປຍັງຜູ້ຮັບທີ່ຖືກຕ້ອງ
                if ($document->document_type_id == 1) { 
                    // ຄົ້ນຫາຜູ້ໃຊ້ທຸກຄົນທີ່ມີ Role ເປັນ Accountant
                    $financeprepares = User::whereHas('role', function ($query) {
                        $query->where('name', 'Finance_Preparer');
                    })->get();

                    // ສົ່ງ Notification ໄປໃຫ້ Accountant ທຸກຄົນ
                    foreach ($financeprepares as $prepare) {
                        // ເຮົາຄວນສ້າງ Notification ໃໝ່ທີ່ຂໍ້ຄວາມເໝາະສົມກວ່າ
                        // ເຊັ່ນ new DocumentForwarded($document)
                        $prepare->notify(new DocumentSubmitted($document));
                    }
                } elseif ($document->document_type_id == 2) {
                    // ຄົ້ນຫາຜູ້ໃຊ້ທຸກຄົນທີ່ມີ Role ເປັນ Accountant
                    $deans = User::whereHas('role', function ($query) {
                        $query->where('name', 'Dean');
                    })->get();

                    // ສົ່ງ Notification ໄປໃຫ້ Accountant ທຸກຄົນ
                    foreach ($deans as $dean) {
                    // ເຮົາຄວນສ້າງ Notification ໃໝ່ທີ່ຂໍ້ຄວາມເໝາະສົມກວ່າ
                    // ເຊັ່ນ new DocumentForwarded($document)
                        $dean->notify(new DocumentSubmitted($document));
                    }
                }

                DB::commit();

                // 8. Redirect ກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມສຳເລັດ
                return redirect()->route('secretary.dashboard')->with('success', 'ອະນຸມັດເອກະສານສຳເລັດແລ້ວ.');
            } elseif ($action === 'reject') {
                // 1. ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນທີ່ສົ່ງມາ (ເຫດຜົນ)
                $request->validate([
                    'rejection_reason' => 'required|string|min:10',
                ]);

                // 2. ກວດສອບເພື່ອຄວາມແນ່ນອນວ່າເອກະສານຢູ່ໃນສະຖານະທີ່ຖືກຕ້ອງ
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
                    'action' => 'Rejected by Secretary',
                    'comment' => $request->input('rejection_reason')
                ]);

                // 6. ค้นหาและส่ง Notification ไปหา Accountant ทุกคน
                $requester = $document->requester; // ເຮົາຕ້ອງສ້າງ Relationship ນີ້

                if ($requester) {
                // ສົ່ງ Notification ກັບໄປຫາຜູ້ສ້າງ
                    $requester->notify(new DocumentRejected($document, auth()->user())); // ສົ່ງຂໍ້ມູນຜູ້ປະຕິເສດໄປພ້ອມ
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

                DB::commit();

                // 7. ສົ່ງກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມແຈ້ງເຕືອນ
                return redirect()->route('secretary.dashboard')->with('success', 'ການສົ່ງເອກະສານກັບສຳເລັດແລ້ວ.');
            }
            /// ถ้า $action ไม่ใช่ทั้ง approve และ reject (กรณีผิดพลาด)
            DB::rollBack(); // ต้อง Rollback ก่อน throw
            throw new \Exception('Invalid action specified.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // จัดการ Validation Exception โดยเฉพาะ
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage());
        }
    }

    /**
    * แสดงประวัติเอกสารที่อนุมัติโดยเลขาคนปัจจุบัน.
    */
    public function approvedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        // 1. ค้นหา ID ของเอกสารทั้งหมดที่เลขาคนนี้เคย "อนุมัติ"
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Approved by Secretary')
                                          ->pluck('document_id');

        // 2. ดึงข้อมูลเอกสารเหล่านั้นมาแสดงผล, พร้อมกับฟังก์ชันค้นหา/กรอง
        $query = Document::whereIn('id', $documentIds);
    
        // ===== เพิ่ม Logic การกรอง (เหมือนใน index()) =====
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
        return view('secretary.history.approved', compact('documents', 'departments', 'statuses'));
    }

    /**
    * แสดงประวัติเอกสารที่ปฏิเสธโดยเลขาคนปัจจุบัน.
    */
    public function rejectedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Rejected by Secretary')
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
        // ===================================
    
        $documents = $query->with('requester.department', 'documentType')
                       ->latest('updated_at')
                       ->paginate(15);
        
        $departments = \App\Models\Department::orderBy('name')->get();
        $statuses = [ // เราสามารถกำหนดสถานะที่เกี่ยวข้องกับหน้านี้ได้
                    'REJECTED' => 'ຖືກປະຕິເສດ',
                ];

        return view('secretary.history.rejected', compact('documents', 'departments', 'statuses'));
    }
}


