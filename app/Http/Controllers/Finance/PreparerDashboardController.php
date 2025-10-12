<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Import User Model
use App\Notifications\DocumentSubmitted; 
use App\Notifications\DocumentRejected;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Finance\PreparerDashboardController;
use Illuminate\Support\Facades\DB;

use App\Notifications\PrivateNoteReceived; // <-- Import Class ใหม่

class PreparerDashboardController extends Controller
{
    use AuthorizesRequests;
    /**
     * ສະແດງ dashboard ຂອງຝ່າຍກະກຽມເອກະສານ.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Document::class);

        // 1. เริ่ม Query ที่สถานะที่เกี่ยวข้อง
        $query = Document::where('status', 'PENDING_FINANCE_PREPARER_REVIEW');

         // ===== เพิ่ม Logic การกรอง =====
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

        // ດຶງຂໍ້ມູນສະເພາະເອກະສານທີ່ລໍຖ້າການກວດສອບຈາກຝ່າຍກະກຽມເອກະສານ
        $pendingDocuments = Document::where('status', 'PENDING_FINANCE_PREPARER_REVIEW')
                                    ->with('requester.department', 'documentType')
                                    ->latest()
                                    ->paginate(15);
        // 3. ดึงข้อมูลที่กรองแล้ว
        $pendingDocuments = $query->with('requester.department', 'documentType')
                              ->latest()
                              ->paginate(15)
                              ->withQueryString();
        
        // 4. ดึงข้อมูลสำหรับ Dropdowns
        $departments = \App\Models\Department::orderBy('name')->get();
        // (หน้านี้ไม่จำเป็นต้องมีตัวกรองสถานะ เพราะแสดงแค่สถานะเดียว)

        // 5. ส่งข้อมูลทั้งหมดไปยัง View
        return view('finance.preparer.dashboard', compact('pendingDocuments', 'departments'));
    }
/*
    public function show(Document $document)
    {
        $document->load('documentType', 'documentItems', 'attachments', 'requester.department', 'documentLogs.user.role');
        
        // ค้นหาผู้ใช้ตาม Role ที่สามารถส่งโน้ตไปหาได้
        $nextRoles = ['Accountant', 'Vice_Dean', 'Head_of_Finance', 'Dean'];
        $potentialRecipients = User::whereHas('role', function ($q) use ($nextRoles) {
            $q->whereIn('name', $nextRoles);
        })->get();
        
        return view('finance.preparer.documents.show', compact('document', 'potentialRecipients'));
    }*/

    public function show(Document $document)
    {
        // ດຶງຂໍ້ມູນທີ່ກ່ຽວຂ້ອງທັງໝົດມາພ້ອມກັນ
        $document->load('documentType', 'documentItems', 'attachments', 'requester.department', 'documentLogs.user.role');
        
        // ค้นหาผู้ใช้ตาม Role ที่เจาะจง
        /*
        $nextRoles = ['Accountant', 'Vice_Dean', 'Head_of_Finance', 'Dean'];
        $potentialRecipients = \App\Models\User::whereHas('role', function ($q) use ($nextRoles) {
        $q->whereIn('name', $nextRoles);
        })->get();*/

        $recipientAccountant = \App\Models\User::whereHas('role', function ($q) { $q->where('name', 'Accountant'); })->first();
        $recipientHeadFinance = \App\Models\User::whereHas('role', function ($q) { $q->where('name', 'Head_of_Finance'); })->first();
        $recipientViceDean = \App\Models\User::whereHas('role', function ($q) { $q->where('name', 'Vice_Dean'); })->first();
        $recipientDean = \App\Models\User::whereHas('role', function ($q) { $q->where('name', 'Dean'); })->first();

        // ຕໍ່ໄປເຮົາຈະສ້າງ View ນີ້
        //return view('finance.preparer.documents.show', compact('document','potentialRecipients'));
        return view('finance.preparer.documents.show', compact(
            'document',
            'recipientAccountant',
            'recipientHeadFinance',
            'recipientViceDean',
            'recipientDean'
        ));
    }

    public function process(Request $request, Document $document)
    {
        //dd($request->all());
        // 1. ตรวจสอบสถานะก่อน
        if ($document->status !== 'PENDING_FINANCE_PREPARER_REVIEW') {
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
            /*
            if ($request->has('notes') && is_array($request->notes)) {
                foreach ($request->notes as $noteData) {
                    // ตรวจสอบว่ามีทั้ง "ผู้รับ" และ "ข้อความ"
                    if (!empty($noteData['recipient_ids']) && is_array($noteData['recipient_ids']) && !empty($noteData['message'])) {
                        
                        $recipients = \App\Models\User::find($noteData['recipient_ids']);
                        $noteContent = $noteData['message'];
                        // วน Loop เพื่อบันทึกโน้ตนี้ให้กับผู้รับทุกคนที่ถูกเลือก
                        foreach ($noteData['recipient_ids'] as $recipientId) {
                            \App\Models\PrivateNote::create([
                                'document_id' => $document->id,
                                'sender_id' => auth()->id(),
                                'recipient_id' => $recipientId,
                                'note' => $noteData['message'],
                            ]);

                            // (ส่ง Notification พิเศษเกี่ยวกับโน้ตนี้)
                            $recipient->notify(new \App\Notifications\PrivateNoteReceived($document, auth()->user(), $noteContent));
                        }
                    }
                }
            }*/
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

                // 2. ປ່ຽນສະຖານະຂອງເອກະສານໄປຂັ້ນຕອນຕໍ່ໄປ
                $document->status = 'PENDING_ACCOUNTANT_BUDGET_CHECK';
                $document->save();

                // 3. ບັນທຶກປະຫວັດການດຳເນີນການ (Log)
                $document->documentLogs()->create([
                    'user_id' => Auth::id(),
                    'action' => 'Approved by Finance Preparer',
                    'comment' => 'ເອກະສານຖືກຕ້ອງຕາມລະບຽບຫຼັກການ, ສົ່ງຕໍ່ໃຫ້ຜູ້ກວດສອບງົບປະມານ ແລະ ເຮັດບັນຊີ.'
                ]);
        
                // ຄົ້ນຫາຜູ້ໃຊ້ທຸກຄົນທີ່ມີ Role ເປັນ Accountant
                $accountants = User::whereHas('role', function ($query) {
                    $query->where('name', 'Accountant');
                })->get();

                // ສົ່ງ Notification ໄປໃຫ້ Accountant ທຸກຄົນ
                // ເຮົາສາມາດສ້າງ Notification Class ໃໝ່ ຫຼືປັບປຸງຂອງເດີມໃຫ້ຢຶດຢຸ່ນຂື້ນ
                // ເພື່ອຄວາມງ່າຍ, ເຮົາຈະໃຊ້ DocumentSubmitted ໄປກ່ອນ
                foreach ($accountants as $accountant) {
                    // ເຮົາຄວນສ້າງ Notification ໃໝ່ທີ່ຂໍ້ຄວາມເໝາະສົມກວ່າ
                    // ເຊັ່ນ new DocumentForwarded($document)
                    $accountant->notify(new DocumentSubmitted($document));
                }

                DB::commit();

                // 4. ສົ່ງກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມແຈ້ງເຕືອນ
                return redirect()->route('finance.preparer.dashboard')->with('success', 'ອະນຸມັດເອກະສານສຳເລັດແລ້ວ.');
            } elseif ($action === 'reject') {
                $request->validate([
                    'rejection_reason' => 'required|string|min:10',
                ]);

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
                    'action' => 'Rejected by Finance Preparer',
                    'comment' => $request->input('rejection_reason')
                ]);

                // 6. ດືງຂໍ້ມູນຜູ້ສ້າງເອກະສານ (Requester)
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
                return redirect()->route('finance.preparer.dashboard')->with('success', 'ປະຕິເສດເອກະສານສຳເລັດແລ້ວ.');
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
    * Approve the document and move it to the next step in the workflow.
    */
/*
    public function approve(Request $request, Document $document)
    {
        // 1. ກວດສອບເພື່ອຄວາມແນ່ນອນວ່າເອກະສານຢູ່ໃນສະຖານະທີ່ຖືກຕ້ອງ
        if ($document->status !== 'PENDING_FINANCE_PREPARER_REVIEW') {
            return back()->with('error', 'ເອກະສານນີ້ບໍ່ໄດ້ຢູ່ໃນສະຖານະທີ່ລໍຖ້າການກວດສອບ.');
        }

        // ຄົ້ນຫາ ແລະ ອັບເດດການແຈ້ງເຕືອນທີ່ກ່ຽວຂ້ອງກັບເອກະສານນີ້ ໃຫ້ເປັນ "ອ່ານແລ້ວ"
        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();

        // 2. ປ່ຽນສະຖານະຂອງເອກະສານໄປຂັ້ນຕອນຕໍ່ໄປ
        $document->status = 'PENDING_ACCOUNTANT_BUDGET_CHECK';
        $document->save();
*/
        // 3. ບັນທຶກປະຫວັດການດຳເນີນການ (Log)
        /*
        $document->documentLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'Approved by Finance Preparer',
            'comment' => $request->input('approval_comment') ?? 'ເອກະສານຖືກຕ້ອງຕາມລະບຽບຫຼັກການ, ສົ່ງຕໍ່ໃຫ້ຜູ້ກວດສອບງົບປະມານ ແລະ ເຮັດບັນຊີ.' // <-- ใช้ Note ที่ส่งมา
        ]);*/
/*        
        $document->documentLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'Approved by Finance Preparer',
            'comment' => 'ເອກະສານຖືກຕ້ອງຕາມລະບຽບຫຼັກການ, ສົ່ງຕໍ່ໃຫ້ຜູ້ກວດສອບງົບປະມານ ແລະ ເຮັດບັນຊີ.'
        ]);
        
        // ຄົ້ນຫາຜູ້ໃຊ້ທຸກຄົນທີ່ມີ Role ເປັນ Accountant
        $accountants = User::whereHas('role', function ($query) {
            $query->where('name', 'Accountant');
        })->get();

        // ສົ່ງ Notification ໄປໃຫ້ Accountant ທຸກຄົນ
        // ເຮົາສາມາດສ້າງ Notification Class ໃໝ່ ຫຼືປັບປຸງຂອງເດີມໃຫ້ຢຶດຢຸ່ນຂື້ນ
        // ເພື່ອຄວາມງ່າຍ, ເຮົາຈະໃຊ້ DocumentSubmitted ໄປກ່ອນ
        foreach ($accountants as $accountant) {
            // ເຮົາຄວນສ້າງ Notification ໃໝ່ທີ່ຂໍ້ຄວາມເໝາະສົມກວ່າ
            // ເຊັ່ນ new DocumentForwarded($document)
            $accountant->notify(new DocumentSubmitted($document));
        }

        // 4. ສົ່ງກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມແຈ້ງເຕືອນ
        return redirect()->route('finance.preparer.dashboard')->with('success', 'ອະນຸມັດເອກະສານສຳເລັດແລ້ວ.');
    }
*/
    /**
    * Reject the document and send it back to the requester.
    */
    /*
    public function reject(Request $request, Document $document)
    {
        // 1. ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນທີ່ສົ່ງມາ (ເຫດຜົນ)
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);
    
        // 2. ກວດສອບສະຖານະເອກະສານ
        if ($document->status !== 'PENDING_FINANCE_PREPARER_REVIEW') {
            return back()->with('error', 'ເອກະສານນີ້ບໍ່ໄດ້ຢູ່ໃນສະຖານະທີ່ລໍຖ້າການກວດສອບ.');
        }

        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();
            
        // 3. ປ່ຽນສະຖານະເອກະສານເປັນ REJECTED ແລະ ບັນທຶກເຫດຜົນ
        $document->status = 'REJECTED';
        $document->rejected_reason = $request->input('rejection_reason');
        $document->save();
    
        // 4. ບັນທຶກປະຫວັດການດຳເນີນການ (Log)
        $document->documentLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'Rejected by Finance Preparer',
            'comment' => $request->input('rejection_reason')
        ]);

        // ດືງຂໍ້ມູນຜູ້ສ້າງເອກະສານ (Requester)
        $requester = $document->requester; // ເຮົາຕ້ອງສ້າງ Relationship ນີ້
        
        if ($requester) {
        // ສົ່ງ Notification ກັບໄປຫາຜູ້ສ້າງ
            $requester->notify(new DocumentRejected($document, auth()->user())); // ສົ່ງຂໍ້ມູນຜູ້ປະຕິເສດໄປພ້ອມ
        }

        // 5. ສົ່ງກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມແຈ້ງເຕືອນ
        return redirect()->route('finance.preparer.dashboard')->with('success', 'ປະຕິເສດເອກະສານສຳເລັດແລ້ວ.');
    }
*/    
    public function approvedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        // 1. ค้นหา ID ของเอกสารทั้งหมดที่เลขาคนนี้เคย "อนุมัติ"
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Approved by Finance Preparer')
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
        return view('finance.preparer.history.approved', compact('documents', 'departments', 'statuses'));
    }
    /*
    public function approvedHistory(Request $request)
    {
        // 1. ค้นหา ID ของเอกสารทั้งหมดที่ผู้ใช้คนนี้เคย "อนุมัติ"
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          // !!! แก้ไข Action ให้ถูกต้อง !!!
                                          ->where('action', 'Approved by Finance Preparer')
                                          ->pluck('document_id');

        // 2. ดึงข้อมูลเอกสารเหล่านั้นมาแสดงผล
        $documents = \App\Models\Document::whereIn('id', $documentIds)
                                     ->with('requester.department', 'documentType')
                                     ->latest('updated_at')
                                     ->paginate(15);
    
        return view('finance.preparer.history.approved', compact('documents'));
    }
    */
    /**
    * แสดงประวัติเอกสารที่ปฏิเสธโดยเลขาคนปัจจุบัน.
    */
    public function rejectedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Rejected by Finance Preparer')
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

        return view('finance.preparer.history.rejected', compact('documents', 'departments', 'statuses'));
    }
}