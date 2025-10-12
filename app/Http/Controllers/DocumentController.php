<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use App\Models\DocumentType;
use Illuminate\Support\Facades\DB; // Import DB facade for transactions
use App\Models\User; // Import User Model
use App\Notifications\DocumentSubmitted; // Import Notification Class
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Document::class);

        // 1. ดึงข้อมูลผู้ใช้
        $user = Auth::user();
    
        // 2. ดึงชื่อภาคส่วน
        $departmentName = $user->department->name ?? 'ບໍ່ໄດ້ລະບຸພາກສ່ວນ';

        // เริ่ม Query
        $query = Document::where('department_id', $user->department_id);

        // ===== เพิ่ม Logic การกรอง =====
        if ($request->filled('doc_code')) {
            $query->where('document_code', 'like', '%' . $request->doc_code . '%');
        }
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // ============================

        // 3. ดึงรายการเอกสาร (โค้ดส่วนนี้ของคุณถูกต้องอยู่แล้ว)
        $documents = Document::query()
            ->where('department_id', $user->department_id)
            ->with('documentType', 'requester')
            ->latest()
            ->paginate(15);
    
        // ดึงข้อมูลสถานะสำหรับ Dropdown
        $statuses = get_available_statuses(); // เราจะสร้าง Helper นี้

        // 4. ส่งข้อมูล "ทั้งสองอย่าง" ไปยัง View
        return view('staff.documents.index', compact('documents', 'departmentName', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // ດືງຂໍ້ມູນປະເພດເອກະສານທັງໝົດສໍາຫຼັບ Dropdown
        $documentTypes = DocumentType::all();

        // ສະແດງໜ້າຟອມສ້າງເອກະສານ
        return view('staff.documents.create', compact('documentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        $action = $request->input('action'); // ดึงค่าจากปุ่มที่กด

        // 1. กำหนด Validation Rules
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'document_type_id' => 'required|exists:document_types,id',
            'activity_description' => 'required|string',
            'references' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];

        // ถ้าเป็นการ "ส่ง" (Submit), ให้บังคับว่าต้องมี Items
        if ($action === 'submit' && $request->input('document_type_id') == 1) {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.item_description'] = 'required|string|max:255';
            $rules['items.*.quantity'] = 'required|numeric|min:1';
            $rules['items.*.unit_price'] = 'required|numeric|min:0';
        }

        $validatedData = $request->validate($rules);
        //dd('ຜ່ານ Validation ແລ້ວ', $validatedData);
        DB::beginTransaction();
        try {
            // 2. กำหนดสถานะตาม Action
            $status = ($action === 'save_draft') ? 'DRAFT' : 'PENDING_SECRETARY_REVIEW';

            // 3. สร้าง Document
            $currentYear = now()->year;
            $latestDocumentInYear = Document::whereYear('created_at', $currentYear)->latest('id')->first();
            $nextSequence = $latestDocumentInYear ? ((int)substr($latestDocumentInYear->document_code, 0, 3)) + 1 : 1;
            $sequenceCode = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
            $departmentCode = str_pad(Auth::user()->department_id, 2, '0', STR_PAD_LEFT);
            $documentCode = "{$sequenceCode}-{$departmentCode}-{$currentYear}";

            // 4. สร้าง Document
            $document = Document::create([
                'document_code' => $documentCode,
                'title' => $validatedData['title'],
                'references' => $validatedData['references'] ?? null,
                'activity_description' => $validatedData['activity_description'],
                'document_type_id' => $validatedData['document_type_id'],
                'requester_id' => Auth::id(),
                'department_id' => Auth::user()->department_id,
                'status' => $status, // <-- ใช้สถานะใหม่
                'total_amount' => 0,
            ]);
            //dd('ສ້າງ Document ສໍາເລັດແລ້ວ', $document);
            // ... (Logic การบันทึก Items และ Attachments เหมือนเดิม) ...
            // 5. บันทึก Items และคำนวณ Total Amount
            $totalAmount = 0;
            if ($request->has('items') && is_array($request->items)) {
                // ใช้ $request->items แทน $validatedData['items']
                foreach ($request->items as $itemData) { 
                // อาจจะต้อง Validate ข้อมูล item ภายในนี้อีกครั้งเพื่อความปลอดภัย
                    $totalPrice = ($itemData['quantity'] ?? 0) * ($itemData['unit_price'] ?? 0);
                    $document->documentItems()->create([
                        'item_description' => $itemData['item_description'] ?? '',
                        'quantity' => $itemData['quantity'] ?? 0,
                        'unit_price' => $itemData['unit_price'] ?? 0,
                        'total_price' => $totalPrice,
                    ]);
                    $totalAmount += $totalPrice;
                }
            }
            // !!! แก้ไข: อัปเดต total_amount ทุกครั้ง, แม้ว่าจะเป็น 0 !!!
            $document->total_amount = $totalAmount;
            $document->save();
            
            // 6. บันทึก Attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    // 1. สร้าง Path สำหรับเก็บไฟล์
                    // ผลลัพธ์จะเป็น "attachments/ชื่อไฟล์ที่ไม่ซ้ำกัน.นามสกุล"
                    // 'public' disk หมายถึงจะเก็บไว้ที่ storage/app/public/
                    $path = $file->store('attachments', 'public');

                    // 2. บันทึกข้อมูลไฟล์ลงในฐานข้อมูล
                    $document->attachments()->create([
                        'file_name' => $file->getClientOriginalName(), // ชื่อไฟล์เดิม
                        'file_path' => $path,           // Path ที่เก็บไฟล์
                    ]);
                }
            }

            DB::commit();
            //dd('Commit ສໍາເລັດແລ້ວ'); // <-- จุดทดสอบที่ 3
            // 4. ส่ง Notification เฉพาะเมื่อเป็นการ "ส่ง"
            if ($action === 'submit') {
                $secretaries = User::whereHas('role', function ($q) { $q->where('name', 'Dean_Secretary'); })->get();
                foreach ($secretaries as $secretary) {
                    $secretary->notify(new DocumentSubmitted($document));
                }
            }

            // 5. กำหนดข้อความแจ้งเตือนตาม Action
            $message = ($action === 'save_draft') ? 'ບັນທຶກເອກະສານສະບັບຮ່າງສໍາເລັດແລ້ວ' : 'ສ້າງເອກະສານ ແລະ ສົ່ງຮຽບຮ້ອຍແລ້ວ';
            return redirect()->route('staff.documents.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            //dd('ເກີດ Exception!', $e->getMessage());
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage())->withInput();
        }
    }

    /**
    * Display the specified resource.
    */
    public function show(Document $document) // ໃຊ້ Route Model Binding
    {
        // ใช้ Policy จะดีกว่าในระยะยาว
        $this->authorize('view', $document);

        // ດຶງຂໍ້ມູນທີ່ກ່ຽວຂ້ອງທັງໝົດມາພ້ອມກັນ (Eager Loading)
        $document->load('documentType', 'documentItems', 'attachments', 'requester.department', 'documentLogs.user.role');

        return view('staff.documents.show', compact('document'));
    }

    /**
    * แสดงฟอร์มสำหรับแก้ไขเอกสาร.
    */
    public function edit(Document $document)
    {
        // 1. ตรวจสอบสิทธิ์โดยใช้ Policy (วิธีเดียวก็เพียงพอ)
        $this->authorize('update', $document); // <-- ใช้ Policy 'update'

        // 2. Mark notification as read
        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();

        // 3. ดึงข้อมูลที่จำเป็นสำหรับฟอร์ม
        $documentTypes = DocumentType::all();
        $document->load('documentItems'); // โหลด Items เพื่อใช้ใน View

        // 4. แสดงหน้าฟอร์มแก้ไข
        return view('staff.documents.edit', compact('document', 'documentTypes'));
    }

    /**
    * อัปเดตเอกสารที่แก้ไขแล้ว.
    */
    public function update(Request $request, Document $document)
    {
        // 1. ตรวจสอบสิทธิ์ (เหมือนใน edit())
        $this->authorize('update', $document);
        $action = $request->input('action');

        // 2. กำหนด Validation Rules (แบบ Dynamic)
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'activity_description' => ['required', 'string'],
            'references' => ['nullable', 'string'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];

        if ($action === 'submit') {
            // ถ้าเป็นการ "ส่ง", ให้บังคับกรอก activity_description
            $rules['activity_description'] = ['required', 'string'];

            // และถ้าเป็น "ขอถอนเงิน", ให้บังคับกรอก items ด้วย
            if ($request->input('document_type_id') == 1) {
                $rules['items'] = ['required', 'array', 'min:1'];
                $rules['items.*.item_description'] = ['required', 'string', 'max:255'];
                $rules['items.*.quantity'] = ['required', 'numeric', 'min:1'];
                $rules['items.*.unit_price'] = ['required', 'numeric', 'min:0'];
            }
        } else { // ถ้า action คือ 'save_draft'
            // ถ้าเป็นการ "บันทึกฉบับร่าง", ไม่ต้องบังคับกรอก activity_description
            $rules['activity_description'] = ['nullable', 'string'];
        }
    
        $validatedData = $request->validate($rules); 

        DB::beginTransaction();
        try {
            $nextStatus = '';
            // ตรวจสอบว่ามี "สถานะก่อนปฏิเสธ" บันทึกไว้หรือไม่
            if (!empty($document->status_before_rejected)) {
                // ถ้ามี, ให้ส่งกลับไปที่สถานะนั้น
                $nextStatus = $document->status_before_rejected;
            } else {
                // ถ้าไม่มี (เป็นฉบับร่างที่ส่งครั้งแรก), ให้ส่งไปที่เลขา
                $nextStatus = 'PENDING_SECRETARY_REVIEW';
            }

            $status = ($action === 'save_draft') ? 'DRAFT' : $nextStatus;

            // 1. สร้าง Array ของข้อมูลที่จะอัปเดต
            $updateData = [
                'title' => $validatedData['title'],
                'document_type_id' => $validatedData['document_type_id'],
                'references' => $validatedData['references'] ?? null,
                'activity_description' => $validatedData['activity_description'],
                'status' => $status,
            ];

            // 2. เพิ่มข้อมูลที่จะล้าง ก็ต่อเมื่อเป็นการ "ส่ง"
            if ($action === 'submit') {
                $updateData['rejected_reason'] = null;
                $updateData['status_before_rejected'] = null;
            }

            // 3. ทำการอัปเดตข้อมูลทั้งหมดในครั้งเดียว
            $document->update($updateData);
            // 4. อัปเดตข้อมูลหลักของเอกสาร

            // 5. ลบ Items และ Attachments เก่า, แล้วสร้างใหม่
            $document->documentItems()->delete();
            
            // 6. บันทึก Items ใหม่ และคำนวณ Total Amount
            $totalAmount = 0;
            // ใช้ $request->items แทน $validatedData['items'] เพื่อความแน่นอน
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $itemData) {
                    // ตรวจสอบให้แน่ใจว่า key ที่ใช้ตรงกับ name ใน input ของคุณ
                    $description = $itemData['item_description'] ?? ($itemData['description'] ?? null);
                    if (empty($description)) continue; // ข้ามรายการที่ว่าง

                    $quantity = $itemData['quantity'] ?? 0;
                    $unitPrice = $itemData['unit_price'] ?? 0;
                    $totalPrice = $quantity * $unitPrice;

                    $document->documentItems()->create([
                        'item_description' => $description,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ]);
                    $totalAmount += $totalPrice;
                }
            }
            $document->total_amount = $totalAmount;
            $document->save();
            
            // 7. บันทึก Attachments ใหม่
            if ($request->hasFile('attachments')) {
                // 1. ลบไฟล์เก่าทั้งหมดออกจาก Storage
                foreach ($document->attachments as $oldAttachment) {
                    Storage::disk('public')->delete($oldAttachment->file_path);
                }

                // 2. ลบข้อมูลไฟล์เก่าทั้งหมดออกจากฐานข้อมูล
                $document->attachments()->delete();

                // 3. อัปโหลดและบันทึกไฟล์ใหม่ (เหมือนกับใน store() method)
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments', 'public');
                    $document->attachments()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                    ]);
                }
            }
            
            DB::commit();
            
            // 8. ส่ง Notification เฉพาะเมื่อเป็นการ "ส่ง"
            if ($action === 'submit') {
                $recipientRoleName = getRoleNameFromStatus($nextStatus); 
                if ($recipientRoleName) {
                    $recipients = User::whereHas('role', function ($q) use ($recipientRoleName) {
                        $q->where('name', $recipientRoleName);
                    })->get();
                    foreach ($recipients as $recipient) {
                        // ควรใช้ Notification ที่เหมาะสม, เช่น DocumentResubmitted
                        $recipient->notify(new DocumentSubmitted($document));
                    }
                }
            }

            $message = ($action === 'save_draft') ? 'ບັນທຶກເອກະສານສະບັບຮ່າງສໍາເລັດແລ້ວ' : 'ແກ້ໄຂ ແລະ ສົ່ງເອກະສານໃໝ່ຮຽບຮ້ອຍແລ້ວ';
            return redirect()->route('staff.documents.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        // อนุญาตให้ลบได้เฉพาะฉบับร่าง และต้องเป็นเจ้าของเท่านั้น
        if ($document->requester_id !== auth()->id() || $document->status !== 'DRAFT') {
            abort(403);
        }

        $document->delete();
        return redirect()->route('staff.documents.index')->with('success', 'ລືບຟາຍເອກະສານສະບັບຮ່າງສໍາເລັດແລ້ວ');
    }

    /**
    * ส่งเอกสารฉบับร่างเข้าสู่ Workflow.
    */
    public function submitDraft(Document $document)
    {
        // 1. ตรวจสอบสิทธิ์: เฉพาะเจ้าของ และสถานะต้องเป็น DRAFT
        if ($document->requester_id !== auth()->id() || $document->status !== 'DRAFT') {
            abort(403);
        }

        $nextStatus = '';
        if (!empty($document->status_before_rejected)) {
            // ถ้ามี, ให้ส่งกลับไปที่สถานะนั้น
            $nextStatus = $document->status_before_rejected;
        } else {
            // ถ้าไม่มี, ให้ส่งไปที่เลขา
            $nextStatus = 'PENDING_SECRETARY_REVIEW';
        }
        
        $document->update([
            'status' => $nextStatus,
            'status_before_rejected' => null, // ล้างค่าเมื่อส่งแล้ว
            'rejected_reason' => null,      // ล้างค่าเมื่อส่งแล้ว
        ]);

        // 3. บันทึก Log
        $document->documentLogs()->create([
            'user_id' => auth()->id(),
            'action'  => 'Submitted',
            'comment' => 'ເອກະສານຖືກສົ່ງເຂົ້າສູ່ຂະບວນການ'
        ]);

        // 4. ส่ง Notification
        $recipientRoleName = getRoleNameFromStatus($nextStatus); // <-- เรียกใช้ Helper
        if ($recipientRoleName) {
            $recipients = User::whereHas('role', function ($q) use ($recipientRoleName) {
                $q->where('name', $recipientRoleName);
            })->get();
            foreach ($recipients as $recipient) {
                $recipient->notify(new DocumentSubmitted($document));
            }
        }

        // 5. Redirect กลับพร้อมข้อความ
        return redirect()->route('staff.documents.index')->with('success', 'ສົ່ງເອກະສານຮຽບຮ້ອຍແລ້ວ');
    }

    public function submitSent(Document $document)
    {
        // 1. ตรวจสอบสิทธิ์: เฉพาะเจ้าของ และสถานะต้องเป็น DRAFT
        if ($document->requester_id !== auth()->id() || $document->status !== 'DRAFT') {
            abort(403);
        }

        $nextStatus = '';
        if (!empty($document->status_before_rejected)) {
            // ถ้ามี, ให้ส่งกลับไปที่สถานะนั้น
            $nextStatus = $document->status_before_rejected;
        } else {
            // ถ้าไม่มี, ให้ส่งไปที่เลขา
            $nextStatus = 'PENDING_SECRETARY_REVIEW';
        }
        
        $document->update([
            'status' => $nextStatus,
            'status_before_rejected' => null, // ล้างค่าเมื่อส่งแล้ว
            'rejected_reason' => null,      // ล้างค่าเมื่อส่งแล้ว
        ]);

        // 3. บันทึก Log
        $document->documentLogs()->create([
            'user_id' => auth()->id(),
            'action'  => 'Submitted',
            'comment' => 'ເອກະສານຖືກສົ່ງເຂົ້າສູ່ຂະບວນການ'
        ]);

        // 4. ส่ง Notification
        $recipientRoleName = getRoleNameFromStatus($nextStatus); // <-- เรียกใช้ Helper
        if ($recipientRoleName) {
            $recipients = User::whereHas('role', function ($q) use ($recipientRoleName) {
                $q->where('name', $recipientRoleName);
            })->get();
            foreach ($recipients as $recipient) {
                $recipient->notify(new DocumentSubmitted($document));
            }
        }

        // 5. Redirect กลับพร้อมข้อความ
        return redirect()->route('staff.documents.index')->with('success', 'ສົ່ງເອກະສານຮຽບຮ້ອຍແລ້ວ');
    }

    public function approvedHistory(Request $request)
    {
        // Logic นี้เหมือนกับ index() แต่ไม่กรองตามสถานะ
        // และอาจจะแสดงข้อมูลมากกว่า
        $query = Document::where('department_id', auth()->user()->department_id);

        // ... (สามารถเพิ่มการค้นหา/กรองได้ที่นี่) ...
        // ===== เพิ่ม Logic การกรอง =====
        if ($request->filled('doc_code')) {
            $query->where('document_code', 'like', '%' . $request->doc_code . '%');
        }
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // ============================

        $documents = $query->with('requester.department', 'documentType')
                       ->latest('created_at')
                       ->paginate(15);

        $statuses = get_available_statuses();

        // เราจะสร้าง View นี้ต่อไป
        return view('staff.history.approved', compact('documents', 'statuses'));
    }


    /**
    * แสดงประวัติเอกสารที่ปฏิเสธโดยเลขาคนปัจจุบัน.
    */
    public function rejectedHistory(Request $request)
    {
        // วิธีที่ถูกต้องคือการกรองตามสถานะ
        $query = Document::where('requester_id', auth()->id())
                       ->where('status', 'REJECTED');

        // ... (สามารถเพิ่มการค้นหา/กรองได้ที่นี่) ...
        // ===== เพิ่ม Logic การกรอง =====
        if ($request->filled('doc_code')) {
            $query->where('document_code', 'like', '%' . $request->doc_code . '%');
        }
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        // ============================

        $documents = $query->with('requester.department', 'documentType')
                       ->latest('updated_at')
                       ->paginate(15);
    
        return view('staff.history.rejected', compact('documents'));
    }
    
    public function print(Document $document)
    {
        $this->authorize('view', $document); // ໃຊ້ Policy ເພື່ອກວດສອບສິດ
        $document->load('documentType', 'documentItems', 'requester.department');
        $fileName = 'document_' . $document->document_code . '.pdf';
    
        // ເຮົາຈະສ້າງ View ນີ້ຕໍ່ໄປ
        $pdf = PDF::loadView('documents.print.template', compact('document'));
        return $pdf->stream($fileName);
    }
}
