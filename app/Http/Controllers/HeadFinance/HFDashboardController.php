<?php
namespace App\Http\Controllers\HeadFinance;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Import User Model
use App\Notifications\DocumentSubmitted;
use App\Notifications\DocumentRejected;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Notifications\DocumentReturnedForCorrection;
use App\Models\PrivateNote;
use Illuminate\Support\Facades\DB;

class HFDashboardController extends Controller
{
    use AuthorizesRequests;
    /**
     * ສະແດງ dashboard ຂອງຫົວໜ໊າພະແນກການເງິນ.
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

        // ດຶງຂໍ້ມູນສະເພາະເອກະສານທີ່ລໍຖ້າການກວດສອບຈາກຫົວໜ້າພະແນກການເງິນ
        $pendingDocuments = Document::whereIn('status', [
                                        'PENDING_FINANCE_HEAD_APPROVAL',
                                        'PENDING_FINANCE_HEAD_VERIFICATION' 
                                    ])
                                    ->with('requester.department', 'documentType')
                                    ->latest()
                                    ->paginate(10);

        $departments = \App\Models\Department::orderBy('name')->get();

        return view('headfinance.dashboard', compact('pendingDocuments', 'departments'));


    }

    // ແກ້ໄຂໃນ HFDashboardController.php
    public function show(Document $document)
    {
        // ===== 1. ກວດສອບສິດການເຂົ້າເບິ່ງຜ່ານ Policy (ປ່ຽນແທນການກວດສອບສະຖານະແບບເກົ່າ) =====
        $this->authorize('view', $document);
        // =========================================================================

        // 2. ໂຫຼດຂໍ້ມູນທີ່ກ່ຽວຂ້ອງ (ຄືເກົ່າ)
        $document->load('documentType', 'documentItems', 'attachments', 'requester.department', 'documentLogs.user.role');

        // 3. ເພີ່ມການດຶງຂໍ້ມູນຜູ້ຮັບໂນດສົ່ງໃຫ້ View (ຄືເກົ່າ)
        $recipientViceDean = \App\Models\User::whereHas('role', function ($q) { 
            $q->where('name', 'Vice_Dean'); 
        })->first();

        $recipientDean = \App\Models\User::whereHas('role', function ($q) { 
            $q->where('name', 'Dean'); 
        })->first();

        // 4. ສົ່ງຕົວແປທັງໝົດໄປຫາ View (ຄືເກົ່າ)
        return view('headfinance.documents.show', compact(
            'document', 
            'recipientViceDean', 
            'recipientDean'
        ));
    }

    // ເພີ່ມໂຄດນີ້ໃສ່ໃນ Controller ຂອງທັງ 3 ບົດບາດ
    public function allDocuments(Request $request)
    {
        // 1. ດຶງຂໍ້ມູນເອກະສານທັງໝົດ ທີ່ບໍ່ແມ່ນສະບັບຮ່າງ (DRAFT)
        $query = Document::where('status', '!=', 'DRAFT');

        // 2. ຮອງຮັບການຄົ້ນຫາ ແລະ ກັ່ນຕອງຂໍ້ມູນ (ເອີ້ນໃຊ້ຟອມຄົ້ນຫາໄດ້ຄືກັນ)
        if ($request->filled('doc_code')) {
            $query->where('document_code', 'like', '%' . $request->doc_code . '%');
        }
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->with('requester.department', 'documentType')
                           ->latest()
                           ->paginate(15)
                           ->withQueryString();

        $departments = \App\Models\Department::orderBy('name')->get();
        
        // ດຶງສະຖານະທັງໝົດຍົກເວັ້ນສະບັບຮ່າງ ເພື່ອມາເປັນຕົວເລືອກກັ່ນຕອງ
        $statuses = get_all_statuses_translation();
        unset($statuses['DRAFT']);

        // ສົ່ງໄປຫາ View ຂອງແຕ່ລະບົດບາດ:
        // - ສຳລັບ Head_of_Finance: 'headfinance.documents.all'
        $viewPath = '';
        $userRole = auth()->user()->role->name;
        if ($userRole === 'Dean') $viewPath = 'dean.documents.all';
        elseif ($userRole === 'Vice_Dean') $viewPath = 'vicedean.documents.all';
        elseif ($userRole === 'Head_of_Finance') $viewPath = 'headfinance.documents.all';

        return view($viewPath, compact('documents', 'departments', 'statuses'));
    }

    public function process(Request $request, Document $document)
    {
        //dd($request->all());
        // 1. ตรวจสอบสถานะก่อน
        if (!in_array($document->status, ['PENDING_FINANCE_HEAD_APPROVAL', 'PENDING_FINANCE_HEAD_VERIFICATION'])) {
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
            // --- ຈັດການການ Approve/Reject ---
            if ($action === 'approve') {
                // ຄົ້ນຫາ ແລະ ອັບເດດການແຈ້ງເຕືອນທີ່ກ່ຽວຂ້ອງກັບເອກະສານນີ້ ໃຫ້ເປັນ "ອ່ານແລ້ວ"
                Auth::user()->unreadNotifications
                    ->where('data.document_id', $document->id)
                    ->markAsRead();

                $nextStatus = '';
                $logComment = '';
                $recipientRoleName = '';
                
                // ແຍກສາຍທາງ ແລະກຳນົດຄ່າຕາມສະຖານະປັດຈຸບັນ
                if ($document->status === 'PENDING_FINANCE_HEAD_APPROVAL') {
                    $nextStatus = 'PENDING_CASHIER_WITHDRAWAL_SLIP'; // ສົ່ງໄປໃຫ້ຄັງເງິນສົດຕີໃບຖອນ
                    $logComment = 'ຫົວໜ້າພະແນກການເງິນກວດສອບຮອບທີ 1 ແລ້ວ, ສົ່ງຕໍ່ໃຫ້ຄັງເງິນສົດຕີໃບຖອນ.';
                    $recipientRoleName = 'Cashier';
                } elseif ($document->status === 'PENDING_FINANCE_HEAD_VERIFICATION') {
                    $nextStatus = 'PENDING_DEAN_FINAL_APPROVAL'; // ສົ່ງຕໍ່ໃຫ້ຄະນະບໍດີອະນຸມັດຈ່າຍ
                    $logComment = 'ຫົວໜ້າພະແນກການເງິນເຊັນຢັ້ງຢືນໃບຖອນແລ້ວ, ສົ່ງຕໍ່ໃຫ້ຄະນະບໍດີອະນຸມັດຈ່າຍ.';
                    $recipientRoleName = 'Dean';
                }

                // 2. ອັບເດດສະຖານະເອກະສານ
                $document->status = $nextStatus;
                $document->save();

                // 3. ບັນທຶກປະຫວັດການດຳເນີນການ (Log) ໂດຍໃຊ້ຄ່າ $logComment ທີ່ປ່ຽນແປງຕາມສະຖານະ
                $document->documentLogs()->create([
                    'user_id' => Auth::id(),
                    'action' => 'Approved by Head of Finance',
                    'comment' => $logComment
                ]);
        
                // 4. ຄົ້ນຫາຜູ້ໃຊ້ຕາມບົດບາດປາຍທາງແບບ Dynamic ($recipientRoleName)
                if (!empty($recipientRoleName)) {
                    $recipients = User::whereHas('role', function ($query) use ($recipientRoleName) {
                        $query->where('name', $recipientRoleName);
                    })->get();

                    // ສົ່ງ Notification ໄປຫາຜູ້ຮັບທຸກຄົນ (ບໍ່ວ່າຈະເປັນ Cashier ຫຼື Dean)
                    foreach ($recipients as $recipient) {
                        $recipient->notify(new DocumentSubmitted($document));
                    }
                }

                DB::commit();

                // 5. ສົ່ງກັບໄປໜ້າ Dashboard ພ້ອມຂໍ້ຄວາມແຈ້ງເຕືອນ
                return redirect()->route('headfinance.dashboard')->with('success', 'ອະນຸມັດເອກະສານສຳເລັດແລ້ວ.');
            } elseif ($action === 'reject') {
                // 1. Validate เหตุผลที่ส่งกลับ
                $request->validate([
                    'rejection_reason' => 'required|string|min:10',
                ]);

                // 2. Mark notification as read
                Auth::user()->unreadNotifications->where('data.document_id', $document->id)->markAsRead();
    
                // ===== 3. เปลี่ยนสถานะ "กลับไป" ที่ Accountant =====
                $document->status = 'PENDING_ACCOUNTANT_POSTING';
                $document->save();
    
                // 4. บันทึก Log ว่าเป็นการ "ส่งกลับ"
                $document->documentLogs()->create([
                    'user_id' => auth()->id(),
                    'action' => 'Returned by Head of Finance',
                    'comment' => 'ສົ່ງກັບໄປໃຫ້ນາຍບັນຊີແກ້ໄຂ: ' . $request->input('rejection_reason')
                ]);

                // 5. ค้นหาและส่ง Notification ไปหา Accountant
                $accountants = \App\Models\User::whereHas('role', function ($q) {
                    $q->where('name', 'Accountant');
                })->get();
    
                $notification = new \App\Notifications\DocumentReturnedForCorrection($document, auth()->user());
    
                foreach ($accountants as $accountant) {
                    $accountant->notify($notification);
                }
    
                DB::commit();
    
                // 6. Redirect พร้อมข้อความที่ถูกต้อง
                return redirect()->route('headfinance.dashboard')->with('success', 'ສົ່ງເອກະສານກັບໄປໃຫ້ນາຍບັນຊີຮຽບຮ້ອຍແລ້ວ');
            }
            /// ถ้า $action ไม่ใช่ทั้ง approve และ reject (กรณีผิดพลาด)
            DB::rollBack(); // ต้อง Rollback ก่อน throw
            throw new \Exception('Invalid action specified.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // จัดการ Validation Exception โดยเฉพาะ
            DB::rollBack();
            //dd('Validation Failed!', $e->errors());
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            //dd('An Exception Occurred!', $e->getMessage(), $e->getFile(), $e->getLine());
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage());
        }
    }

    public function approvedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        // 1. ค้นหา ID ของเอกสารทั้งหมดที่เลขาคนนี้เคย "อนุมัติ"
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Approved by Head of Finance')
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
        return view('headfinance.history.approved', compact('documents', 'departments', 'statuses'));
    }

    /**
    * แสดงประวัติเอกสารที่ปฏิเสธโดยเลขาคนปัจจุบัน.
    */
    public function rejectedHistory(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Returned by Head of Finance')
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
                
        return view('headfinance.history.rejected', compact('documents', 'departments', 'statuses'));
    }
}
