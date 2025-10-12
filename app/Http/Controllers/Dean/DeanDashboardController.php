<?php

namespace App\Http\Controllers\Dean;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\PrivateNote;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\DocumentSubmitted;
use App\Notifications\DocumentRejected;
use App\Notifications\DocumentForwarded; 
use App\Models\User; // Import User Model
use App\Notifications\DocumentReceivedInDepartment;
use App\Notifications\DocumentWasRejectedToApprover;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 

class DeanDashboardController extends Controller
{
    use AuthorizesRequests;
    /**
     * ສະແດງ dashboard ຂອງຄະນະບໍດີ.
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

        // ດຶງເອກະສານທັງໝົດທີ່ Dean ຕ້ອງດຳເນີນການ
        $pendingDocuments = Document::whereIn('status', [
                                        'PENDING_DEAN_FINAL_APPROVAL', // ວຽກເກົ່າ: ລໍຖ້າອະນຸມັດຈ່າຍ
                                        'PENDING_DEAN_APPROVAL'      // ວຽກໃໝ່: ລໍຖ້າອະນຸມັດຫຼັກການ
                                    ])
                                    ->with('requester.department', 'documentType')
                                    ->latest()
                                    ->paginate(15);

        $departments = \App\Models\Department::orderBy('name')->get();
        
        return view('dean.dashboard', compact('pendingDocuments', 'departments'));
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
        return view('dean.documents.show', compact('document', 'privateNotes'));
    }

    /**
    * Approve the document and move it to the next step in the workflow.
    */
    public function approve(Request $request, Document $document)
    {
        // 1. ตรวจสอบสถานะปัจจุบัน
        if (!in_array($document->status, ['PENDING_DEAN_FINAL_APPROVAL', 'PENDING_DEAN_APPROVAL'])) {
            return back()->with('error', 'ເອກະສານນີ້ບໍ່ໄດ້อยู่ในສະຖານະທີ່ລໍຖ້າການອະນຸມັດ.');
        }

        DB::beginTransaction();
        try {
            // 2. Mark notification as read
            Auth::user()->unreadNotifications->where('data.document_id', $document->id)->markAsRead();

            // 3. กำหนดค่าเริ่มต้น
            $nextStatus = '';
            $recipientRoleName = '';
            $logComment = '';
            $logAction = 'Approved by Dean'; // Action เริ่มต้น

            // 4. แยกสายทาง และกำหนดค่าทั้งหมดในครั้งเดียว
            if ($document->status === 'PENDING_DEAN_FINAL_APPROVAL') {
                $nextStatus = 'READY_FOR_PAYMENT';
                $logComment = 'ອະນຸມັດຈ່າຍຂັ້ນສຸດທ້າຍ.';
                $recipientRoleName = 'Cashier';
            } elseif ($document->status === 'PENDING_DEAN_APPROVAL') {
                $nextStatus = 'PENDING_PROCUREMENT_EVALUATION';
                $logComment = 'ອະນຸມັດຫຼັກການໃຫ້ຈັດຊື້ໄດ້, ສົ່ງຕໍ່ໄປໃຫ້ຝ່າຍຈັດຊື້.';
                $logAction = 'Approved Principle by Dean';
                $recipientRoleName = 'Procurement_Staff';
            }

            // 5. อัปเดตสถานะเอกสาร
            $document->update(['status' => $nextStatus]);

            // 6. บันทึก Log
            $document->documentLogs()->create([
                'user_id' => Auth::id(),
                'action' => $logAction,
                'comment' => $logComment
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກຂໍ້ມູນ: ' . $e->getMessage());
        }
        
        // --- 7. ส่วนการส่ง Notification (ฉบับแก้ไข) ---
        if (!empty($recipientRoleName)) {

            // 7.1 ค้นหาผู้รับหลัก (ตาม Role Name)
            $mainRecipients = User::whereHas('role', function ($query) use ($recipientRoleName) {
                $query->where('name', $recipientRoleName);
            })->get();
    
            // 7.2 ส่ง Notification ที่เหมาะสมไปหาผู้รับหลัก
            if ($mainRecipients->isNotEmpty()) {
                // (ใช้ DocumentSubmitted ไปก่อน, หรือ DocumentForwarded ถ้ามี)
                $notificationForMain = new DocumentSubmitted($document);
                foreach ($mainRecipients->unique('id') as $recipient) {
                    $recipient->notify($notificationForMain);
                }
            }
    
            // =======================================================
            // ===== 7.3 (ใหม่) Logic การแจ้งเตือนคนในแผนกจัดตั้งโดยเฉพาะ =====
            // =======================================================

            // ตรวจสอบว่านี่คือกรณีที่ส่งไปหาฝ่ายจัดซื้อใช่หรือไม่
            if ($recipientRoleName === 'Procurement_Staff') {
        
                $orgDepartmentId = 6; // ID ของแผนกจัดตั้ง
        
                // ค้นหา Staff คนอื่นๆ ในแผนกนั้น (ไม่รวม Procurement Staff)
                $departmentStaff = User::where('department_id', $orgDepartmentId)
                               ->whereHas('role', function ($q) { $q->where('name', 'Staff'); })
                               ->get();
        
                // ถ้าเจอคนในแผนก, ให้ส่ง Notification อีกแบบหนึ่งไป
                if ($departmentStaff->isNotEmpty()) {
                    // สร้าง Notification Class ใหม่สำหรับคนในแผนก
                    $notificationForDept = new \App\Notifications\DocumentReceivedInDepartment($document, auth()->user());
            
                    foreach ($departmentStaff as $staff) {
                        $staff->notify($notificationForDept);
                    }
                }
            }
        }
        /*
        if (!empty($recipientRoleName)) {
            // 7.1 ค้นหาผู้รับหลัก (ตาม Role Name)
            $recipients = User::whereHas('role', function ($query) use ($recipientRoleName) {
                $query->where('name', $recipientRoleName);
            })->get();
    
            // 7.2 (ใหม่) ถ้าเป็นกรณีของ Procurement, ให้หาคนในแผนกจัดตั้งด้วย
            if ($recipientRoleName === 'Procurement_Staff') {
                // ID ของแผนกจัดตั้ง (สมมติว่าคือ 6)
                $orgDepartmentId = 6; 
        
                // ค้นหา Staff คนอื่นๆ ในแผนกนั้น
                $departmentStaff = User::where('department_id', $orgDepartmentId)
                               ->whereHas('role', function ($q) { $q->where('name', 'Staff'); })
                               ->get();
        
                // รวมผู้รับทั้งสองกลุ่มเข้าด้วยกัน
                $recipients = $recipients->merge($departmentStaff);
            }

            // 7.3 ส่ง Notification
            if ($recipients->isNotEmpty()) {
                $notification = new DocumentSubmitted($document);
                foreach ($recipients->unique('id') as $recipient) { // ใช้ unique() เพื่อป้องกันการส่งซ้ำ
                    $recipient->notify($notification);
                }
            }
        }*/
        // --- 7. ส่วนการส่ง Notification (อยู่นอก Transaction) ---
        /*
        if (!empty($recipientRoleName)) {
            $recipients = User::whereHas('role', function ($query) use ($recipientRoleName) {
                $query->where('name', $recipientRoleName);
            })->get();
        
            if ($recipients->isNotEmpty()) {
                // (ใช้ DocumentSubmitted ไปก่อนเพื่อความง่าย)
                $notification = new DocumentSubmitted($document);
                foreach ($recipients as $recipient) {
                    $recipient->notify($notification);
                }
            }
        }*/

        // 8. Redirect
        return redirect()->route('dean.dashboard')->with('success', 'ອະນຸມັດເອກະສານສໍາເລັດແລ້ວ.');
    }

    /*
    public function approve(Document $document)
    {
        // 1. ກວດສອບເພື່ອຄວາມແນ່ນອນວ່າເອກະສານຢູ່ໃນສະຖານະທີ່ຖືກຕ້ອງ
        if (!in_array($document->status, ['PENDING_DEAN_FINAL_APPROVAL', 'PENDING_DEAN_APPROVAL'])) {
            return back()->with('error', 'ເອກະສານນີ້ບໍ່ໄດ້ຢູ່ໃນສະຖານະທີ່ລໍຖ້າການອະນຸມັດ.');
        }

        // ຄົ້ນຫາ ແລະ ອັບເດດການແຈ້ງເຕືອນທີ່ກ່ຽວຂ້ອງກັບເອກະສານນີ້ ໃຫ້ເປັນ "ອ່ານແລ້ວ"
        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();

        // 2. ປ່ຽນສະຖານະຂອງເອກະສານໄປຂັ້ນຕອນຕໍ່ໄປ
        $nextStatus = '';
        $recipients = null;
        if ($document->status === 'PENDING_DEAN_FINAL_APPROVAL') { // ຖ້າເປັນການອະນຸມັດຈ່າຍ
            $nextStatus = 'READY_FOR_PAYMENT';
        } elseif ($document->status === 'PENDING_DEAN_APPROVAL') { // ຖ້າເປັນການອະນຸມັດຫຼັກການຈັດຊື້
            $nextStatus = 'PENDING_PROCUREMENT_EVALUATION'; // ສົ່ງໄປພະແນກຈັດຕັ້ງ
        }

        $document->status = $nextStatus;
        $document->save();

        // 3. ບັນທຶກປະຫວັດການດຳເນີນການ (Log)
        $document->documentLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'Approved by Dean',
            'comment' => 'ຖືກຕ້ອງ ເໝາະສົມ, ສົ່ງຕໍ່ໃຫ້ຄະນະບໍດີອະນຸມັດ.'
        ]);
        
        // ຄົ້ນຫາຜູ້ໃຊ້ທຸກຄົນທີ່ມີ Role ເປັນ Cashier
        $casheirs = User::whereHas('role', function ($query) {
            $query->where('name', 'Cashier');
        })->get();

        // ສົ່ງ Notification ໄປໃຫ້ Vice Dean ທຸກຄົນ
        // ເຮົາສາມາດສ້າງ Notification Class ໃໝ່ ຫຼືປັບປຸງຂອງເດີມໃຫ້ຢຶດຢຸ່ນຂື້ນ
        // ເພື່ອຄວາມງ່າຍ, ເຮົາຈະໃຊ້ DocumentSubmitted ໄປກ່ອນ
        foreach ($casheirs as $cacheir) {
            // ເຮົາຄວນສ້າງ Notification ໃໝ່ທີ່ຂໍ້ຄວາມເໝາະສົມກວ່າ
            // ເຊັ່ນ new DocumentForwarded($document)
            $cacheir->notify(new DocumentSubmitted($document));
        }

        if ($recipients) {
            foreach ($recipients as $recipient) {
                $recipient->notify(new DocumentForwarded($document, auth()->user()));
            }
        }

        // 4. ສົ່ງກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມແຈ້ງເຕືອນ
        return redirect()->route('dean.dashboard')->with('success', 'ອະນຸມັດເອກະສານສຳເລັດແລ້ວ.');
    }
    */

    /**
    * Reject the document and send it back to the requester.
    */
    public function reject(Request $request, Document $document)
    {
        DB::beginTransaction();
        try {
            // --- ย้าย Validation และ Status Check มาไว้ที่นี่ ---
            $request->validate([ 'rejection_reason' => 'required|string|min:10' ]);

            if (!in_array($document->status, ['PENDING_DEAN_FINAL_APPROVAL', 'PENDING_DEAN_APPROVAL'])) {
                throw new \Exception('ເອກະສານບໍ່ໄດ້ຢູ່ໃນສະຖານະທີ່ລໍຖ້າການອະນຸມັດ.');
            }

            Auth::user()->unreadNotifications->where('data.document_id', $document->id)->markAsRead();

            // บันทึกสถานะเก่า
            $document->status_before_rejected = $document->status;
        
            // อัปเดตเอกสาร
            $document->status = 'REJECTED';
            $document->rejected_reason = $request->input('rejection_reason');
            $document->save();
    
            // บันทึก Log
            // 5. ບັນທຶກປະຫວັດການດຳເນີນການ (Log)
            $document->documentLogs()->create([
                'user_id' => Auth::id(),
                'action' => 'Rejected by Dean',
                'comment' => $request->input('rejection_reason')
            ]);
            //dd('บันทึกสถานะและ Log สำเร็จ!');
            // ถ้าทุกอย่างสำเร็จ, ยืนยันการเปลี่ยนแปลง
            DB::commit();

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກຂໍ້ມູນ: ' . $e->getMessage());
        }

        // --- ส่วนการส่ง Notification จะอยู่นอก Transaction ---
        // เพราะเราต้องการให้ข้อมูลถูกบันทึกสำเร็จก่อน ถึงค่อยส่ง Notification

        // ส่ง Notification ไปหา Requester
        $requester = $document->requester;
        if ($requester) {
            $requester->notify(new \App\Notifications\DocumentRejected($document, auth()->user()));
        }

        if ($document->document_type_id == 1) {
            // ส่ง Notification ไปหาผู้ที่เกี่ยวข้อง
            $concernedRoles = [
                'Dean_Secretary',
                'Finance_Preparer',
                'Accountant',
                'Vice_Dean',
                'Head_of_Finance'
            ];

            $recipients = \App\Models\User::whereHas('role', function ($query) use ($concernedRoles) {
                $query->whereIn('name', $concernedRoles);
            })->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(new \App\Notifications\DocumentWasRejectedToApprover($document, auth()->user()));
            }
        } else {
            // ส่ง Notification ไปหาผู้ที่เกี่ยวข้อง
            $concernedRoles = [
                'Dean_Secretary',
            ];

            $recipients = \App\Models\User::whereHas('role', function ($query) use ($concernedRoles) {
                $query->whereIn('name', $concernedRoles);
            })->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(new \App\Notifications\DocumentWasRejectedToApprover($document, auth()->user()));
            }
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
    
        return redirect()->route('dean.dashboard')->with('success', 'ການສົ່ງເອກະສານກັບສຳເລັດແລ້ວ.');
    }
    /*
    public function reject(Request $request, Document $document)
    {
        // 1. ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນທີ່ສົ່ງມາ (ເຫດຜົນ)
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);
    
        // 2. ກວດສອບສະຖານະເອກະສານ
        if ($document->status !== 'PENDING_DEAN_FINAL_APPROVAL') {
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
            'action' => 'Rejected by Dean',
            'comment' => $request->input('rejection_reason')
        ]);

        // 6. ດືງຂໍ້ມູນຜູ້ສ້າງເອກະສານ (Requester)
        $requester = $document->requester; // ເຮົາຕ້ອງສ້າງ Relationship ນີ້
        // 1. แจ้งเตือนผู้สร้าง (Requester) - เหมือนเดิม
        $requester = $document->requester;
        if ($requester) {
            $requester->notify(new \App\Notifications\DocumentRejected($document, auth()->user()));
        }

        // 7. ค้นหาผู้ใช้ใน Role ที่เกี่ยวข้อง
        $concernedRoles = [
            'Dean_Secretary',
            'Finance_Preparer',
            'Accountant',
            'Vice_Dean',
            'Head_of_Finance'
        ];
        // 8. ค้นหาผู้ใช้ทั้งหมดใน Role เหล่านั้น
        $recipients = \App\Models\User::whereHas('role', function ($query) use ($concernedRoles) {
            $query->whereIn('name', $concernedRoles);
        })->get();
        // 9. ส่ง Notification ไปให้ผู้รับทุกคน
        foreach ($recipients as $recipient) {
            $recipient->notify(new \App\Notifications\DocumentWasRejectedToApprover($document, auth()->user()));
        }
        // 10. ສົ່ງກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມແຈ້ງເຕືອນ
        return redirect()->route('dean.dashboard')->with('success', 'ປະຕິເສດເອກະສານສຳເລັດແລ້ວ.');
    }*/

    public function approvedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        // 1. ค้นหา ID ของเอกสารทั้งหมดที่เลขาคนนี้เคย "อนุมัติ"
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Approved by Dean')
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
        return view('dean.history.approved', compact('documents', 'departments', 'statuses'));
    }

    /**
    * แสดงประวัติเอกสารที่ปฏิเสธโดยเลขาคนปัจจุบัน.
    */
    public function rejectedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Rejected by Dean')
                                          ->pluck('document_id');

        $query = Document::whereIn('id', $documentIds);
        
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

        return view('dean.history.rejected', compact('documents', 'departments', 'statuses'));
    }
}