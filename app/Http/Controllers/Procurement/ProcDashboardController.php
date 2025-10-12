<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Document; // <-- Import Document Model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DocumentType;
use App\Models\User; 
use App\Notifications\DocumentSubmitted;
use App\Notifications\DocumentRejected;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;

class ProcDashboardController extends Controller
{
    use AuthorizesRequests;
    /**
     * ສະແດງ dashboard ຂອງຝ່າຍຈັດຊື້.
     *
     * @return \Illuminate\View\View
     */

    public function dashboard(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        $userId = auth()->id();
        $user = auth()->user();

        $departmentName = $user->department->name ?? 'ຝ່າຍຈັດຊື້/ສ້ອມແປງ';


        // 1. งานที่ต้องทำ (Task)
        $tasksQuery = Document::whereIn('status', [
            'PENDING_PROCUREMENT_EVALUATION',
            'PROCUREMENT_IN_PROGRESS',
            'PURCHASE_COMPLETE_PENDING_PAYMENT'
        ]);

        // 2. งานที่ต้องติดตาม (Tracking)
        $trackingQuery = Document::where('requester_id', $userId)
                            ->whereNotNull('parent_document_id');
        
        // --- 3. เพิ่มเงื่อนไขการกรองให้กับ cảสอง Query ---
        $filters = $request->only(['doc_code', 'title', 'department_id', 'date', 'status']);
    
        // ใช้ Loop เพื่อเพิ่มเงื่อนไขการกรองให้กับ ທັງสอง Query
        foreach ([$tasksQuery, $trackingQuery] as $query) {
            if (!empty($filters['doc_code'])) {
                $query->where('document_code', 'like', '%' . $filters['doc_code'] . '%');
            }
            if (!empty($filters['title'])) {
                $query->where('title', 'like', '%' . $filters['title'] . '%');
            }
            if (!empty($filters['department_id'])) {
                $query->where('department_id', $filters['department_id']);
            }
            if (!empty($filters['date'])) {
                $query->whereDate('created_at', $filters['date']);
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
        }
        // --- จบการกรอง ---

        // 4. ดึงข้อมูลและรวมผลลัพธ์
        // ใช้ with() ก่อน union() และ select() คอลัมน์ที่เหมือนกัน
        // เพื่อความง่าย, เราจะใช้ get() และ merge() เหมือนเดิม แต่แก้ไขให้ถูก
    
        $tasks = $tasksQuery->with('requester', 'documentType')->get();
        $tracking = $trackingQuery->with('requester', 'documentType')->get();

        $allDocuments = $tasks->merge($tracking)->sortByDesc('updated_at');

         // 5. Manual Pagination (เหมือนเดิม)
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $currentPageItems = $allDocuments->slice(($currentPage - 1) * $perPage, $perPage);
        $documents = new \Illuminate\Pagination\LengthAwarePaginator($currentPageItems, count($allDocuments), $perPage, $currentPage, ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), ]);

        // 6. ดึงข้อมูลสำหรับ Dropdowns
        $departments = \App\Models\Department::orderBy('name')->get();
        $statuses = get_available_statuses();
        
        // 7. ส่งข้อมูลทั้งหมดไปยัง View
        return view('procurement.dashboard', compact('documents', 'departmentName', 'departments', 'statuses'));
    }
    
    public function show(Document $document)
    {
        // ດຶງຂໍ້ມູນທີ່ກ່ຽວຂ້ອງທັງໝົດມາພ້ອມກັນ
        $document->load('documentType', 'documentItems', 'attachments', 'requester.department', 'documentLogs.user.role');

        // ຕໍ່ໄປເຮົາຈະສ້າງ View ນີ້
        return view('procurement.documents.show', compact('document'));
    }

    // (นี่คือ method ที่จะถูกเรียกโดยลิงก์ "แก้ไข")
    public function edit(Document $document)
    {
        $this->authorize('update', $document); // ใช้ Policy จะดีกว่า
        
        $documentTypes = \App\Models\DocumentType::all();
        $document->load('documentItems');

        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();

        // ใช้ View ของ Staff ร่วมกัน
        return view('staff.documents.edit', compact('document', 'documentTypes'));
    }

    public function update(Request $request, Document $document)
    {
        //dd('Action ທີ່ໄດ້ຮັບຄື:', $request->input('action'));
        // 1. ตรวจสอบสิทธิ์ด้วย Policy
        $this->authorize('update', $document);
    
        $action = $request->input('action');

        // 2. Validation Rules (เหมือนกับ storePaymentRequest)
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'activity_description' => ['required', 'string'],
            'references' => ['nullable', 'string'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];

        if ($action === 'submit') {
            $rules['activity_description'] = ['required', 'string'];
            if ($request->input('document_type_id') == 1) {
                $rules['items'] = ['required', 'array', 'min:1'];
                $rules['items.*.item_description'] = ['required', 'string', 'max:255'];
                $rules['items.*.quantity'] = ['required', 'numeric', 'min:1'];
                $rules['items.*.unit_price'] = ['required', 'numeric', 'min:0'];
            }
        } else { // save_draft
            $rules['activity_description'] = ['nullable', 'string'];
        }
        $validatedData = $request->validate($rules);  

        DB::beginTransaction();
        try {
            $nextStatus = '';
            if (!empty($document->status_before_rejected)) {
                $nextStatus = $document->status_before_rejected;
            } else {
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

            if ($action === 'submit') {
                $updateData['rejected_reason'] = null;
                $updateData['status_before_rejected'] = null;
            }

            $document->update($updateData);

            // 4. อัปเดตข้อมูลหลัก
            /*
            $document->update([
                'title' => $validatedData['title'],
                'document_type_id' => $validatedData['document_type_id'],
                'references' => $validatedData['references'] ?? null,
                'activity_description' => $validatedData['activity_description'] ?? null,
                'status' => $status,
                'rejected_reason' => null,
            ]);*/

            // 5. ลบ Items และ Attachments เก่า, แล้วสร้างใหม่
            $document->documentItems()->delete();
            // (ส่วนการลบไฟล์เก่ายังไม่ได้ทำ, แต่ไม่เป็นไรสำหรับตอนนี้)
        
            // 6. บันทึก Items ใหม่ และคำนวณ Total Amount
            $totalAmount = 0;
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $itemData) {
                    $description = $itemData['item_description'] ?? null;
                    if (empty($description)) continue;
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
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments', 'public');
                    $document->attachments()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                    ]);
                }
            }
        
            // 8. จัดการสถานะของเอกสารจัดซื้อต้นฉบับ (ถ้ามีการ "ส่ง")
            if ($action === 'submit' && $document->parent_document_id) {
                $purchaseDocument = Document::find($document->parent_document_id);
                if ($purchaseDocument) {
                    $purchaseDocument->update(['status' => 'COMPLETED']);
                    $purchaseDocument->documentLogs()->create([
                        'user_id' => auth()->id(),
                        'action' => 'Payment Request Created',
                        'comment' => 'ສ້າງເອກະສານຂໍຖອນເງິນ (ID: ' . $document->id . ') ຮຽບຮ້ອຍແລ້ວ'
                    ]);
                }
            }

            if ($action === 'submit' && $document->wasChanged('status') && $document->getOriginal('status') === 'REJECTED') {
            
                // ค้นหา Staff ทุกคนในแผนกจัดตั้ง
                $orgDepartmentId = $document->department_id;
                $departmentUsers = \App\Models\User::where('department_id', $orgDepartmentId)->get();

                // ทำการ Mark as Read Notification "ถูกปฏิเสธ" ของทุกคนในแผนก
                foreach ($departmentUsers as $user) {
                    $user->unreadNotifications
                        ->where('data.document_id', $document->id)
                        ->where('type', 'App\Notifications\DocumentRejected')
                        ->markAsRead();
                }
            }

            DB::commit();
        
            // 9. ส่ง Notification (ถ้ามีการ "ส่ง")
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
        
            $message = ($action === 'save_draft') ? 'ດັບເດດເອກະສານສະບັບຮ່າງສໍາເລັດແລ້ວ' : 'ແກ້ໄຂແລະສົ່ງເອກະສານໃໝ່ຮຽບຮ້ອຍແລ້ວ';
            return redirect()->route('procurement.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage())->withInput();
        }
    }

    // (นี่คือ method ที่จะถูกเรียกโดยปุ่ม "ส่ง")
    public function submitDraft(Document $document)
    {
        $this->authorize('update', $document); // ใช้ Policy เดียวกัน
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
        
        // 2. เปลี่ยนสถานะ
        //$document->status = 'PENDING_SECRETARY_REVIEW';
        //$document->save();

        // 3. บันทึก Log
        $document->documentLogs()->create([
            'user_id' => auth()->id(),
            'action'  => 'Submitted',
            'comment' => 'ເອກະສານຖືກສົ່ງເຂົ້າສູ່ຂະບວນການ'
        ]);

        // 4. ส่ง Notification
        /*
        $secretaries = User::whereHas('role', function ($q) { $q->where('name', 'Dean_Secretary'); })->get();
        foreach ($secretaries as $secretary) {
            $secretary->notify(new DocumentSubmitted($document));
        }*/

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
        return redirect()->route('procurement.dashboard')->with('success', 'ສົ່ງເອກະສານຮຽບຮ້ອຍແລ້ວ');
    }

    // (นี่คือ method ที่จะถูกเรียกโดยปุ่ม "ลบ")
    public function destroy(Document $document)
    {
        // อนุญาตให้ลบได้เฉพาะฉบับร่าง และต้องเป็นเจ้าของเท่านั้น
        if ($document->requester_id !== auth()->id() || $document->status !== 'DRAFT') {
            abort(403);
        }

        $document->delete();
        return redirect()->route('procurement.dashboard')->with('success', 'ລືບຟາຍເອກະສານສະບັບຮ່າງສໍາເລັດແລ້ວ');
    }

    /**
    * Action 1: ເລີ່ມດຳເນີນການຈັດຊື້
    */
    public function startProcess(Document $document)
    {
        // Mark as Read ການແຈ້ງເຕືອນຂອງເລຂາເອງ
        /*
        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();
        */
        $document->update(['status' => 'PROCUREMENT_IN_PROGRESS']);
    
        $document->documentLogs()->create([
            'user_id' => auth()->id(),
            'action' => 'Procurement Started',
            'comment' => 'ຝ່າຍຈັດຊື້ເລີ່ມດຳເນີນການ'
        ]);

        return redirect()->route('procurement.dashboard')->with('success', 'ເລີ່ມດຳເນີນການຈັດຊື້ແລ້ວ');
    }

    /**
    * Action 2: ຢືນຢັນການຈັດຊື້ສຳເລັດ
    */
    public function completePurchase(Document $document)
    {
        // Mark as Read ການແຈ້ງເຕືອນຂອງເລຂາເອງ
        /*
        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();
            */
        $document->update(['status' => 'PURCHASE_COMPLETE_PENDING_PAYMENT']);
    
        $document->documentLogs()->create([
            'user_id' => auth()->id(),
            'action' => 'Purchase Completed',
            'comment' => 'ດຳເນີນການຈັດຊື້ສຳເລັດ, ລໍຖ້າສ້າງເອກະສານຂໍຖອນເງິນ'
        ]);

        return redirect()->route('procurement.dashboard')->with('success', 'ຢືນຢັນການຈັດຊື້ສຳເລັດແລ້ວ');
    }

    /**
    * Action 3: ສ້າງເອກະສານຂໍຖອນເງິນ
    * ນີ້ຄືຈຸດເຊື່ອມຕໍ່ Workflow ທີ່ສຳຄັນທີ່ສຸດ!
    */
    public function createPaymentRequest(Document $document) // $document ແມ່ນເອກະສານຈັດຊື້
    {
        // Mark as Read ການແຈ້ງເຕືອນຂອງເລຂາເອງ
        Auth::user()->unreadNotifications
            ->where('data.document_id', $document->id)
            ->markAsRead();

        // ກວດສອບສະຖານະກ່ອນ
        if ($document->status !== 'PURCHASE_COMPLETE_PENDING_PAYMENT') {
            return back()->with('error', 'ເອກະສານຍັງບໍ່ໄດ້ກຽມພ້ອມທີ່ຈະສ້າງໜັງສືຂໍຖອນເງິນ');
        }

        $document->load('documentItems');

        // ດືງຂໍ້ມູນປະເພດເອກະສານທັງໝົດ (ເໝືອນໃນ DocumentController ຂອງ Staff)
        $documentTypes = DocumentType::all();

        // ເຮົາຈະໃຊ້ View ດຽວກັນກັບຂອງ Staff (`staff.documents.create`)
        // ແຕ່ເຮົາຈະສົ່ງຂໍ້ມູນຂອງເອກະສານຈັດຊື້ຕົ້ນສະພັບໄປນໍາ
        return view('staff.documents.create', [
            'documentTypes' => $documentTypes,
            'purchaseDocument' => $document, // ສົ່ງເອກະສານຈັດຊື້ໄປໃຫ້ View
        ]);
    }

    public function storePaymentRequest(Request $request)
    {
        // 1. ดึง Action (ถูกต้อง)
        $action = $request->input('action');

        // 2. Validation Rules (ถูกต้อง)
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'parent_document_id' => ['required', 'exists:documents,id'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'references' => ['nullable', 'string'],
        ];

        if ($action === 'submit') {
            $rules['activity_description'] = ['required', 'string'];
            if ($request->input('document_type_id') == 1) {
                $rules['items'] = ['required', 'array', 'min:1'];
                $rules['items.*.item_description'] = ['required', 'string', 'max:255'];
                $rules['items.*.quantity'] = ['required', 'numeric', 'min:1'];
                $rules['items.*.unit_price'] = ['required', 'numeric', 'min:0'];
            }
        } else { // save_draft
            $rules['activity_description'] = ['nullable', 'string'];
        }
        $validatedData = $request->validate($rules);

        DB::beginTransaction();
        try {
            // ค้นหาเอกสารจัดซื้อต้นฉบับจาก parent_document_id ที่ส่งมาจากฟอร์ม
            $purchaseDocument = Document::findOrFail($validatedData['parent_document_id']);

            // 3. สร้างรหัสเอกสาร (ฉบับแก้ไข)
            $departmentIdForCode = 6; // <-- ใช้ ID ของ "พะແນກຈັດຕັ້ງ-ສັງລວມ"
            $currentYear = now()->year;
            $latestDocumentInYear = Document::whereYear('created_at', $currentYear)->latest('id')->first();
            $nextSequence = $latestDocumentInYear ? ((int)substr($latestDocumentInYear->document_code, 0, 3)) + 1 : 1;
            $sequenceCode = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
            $departmentCode = str_pad($departmentIdForCode, 2, '0', STR_PAD_LEFT);
            $documentCode = "{$sequenceCode}-{$departmentCode}-{$currentYear}";

            // 4. กำหนดสถานะ (ถูกต้อง)
            $status = ($action === 'save_draft') ? 'DRAFT' : 'PENDING_SECRETARY_REVIEW';
        
            // 5. สร้างเอกสาร "ขอถอนเงิน" ใหม่
            $paymentDocument = Document::create([
                'document_code' => $documentCode,
                'title' => $validatedData['title'],
                'references' => $validatedData['references'] ?? null,
                'activity_description' => $validatedData['activity_description'] ?? null,
                'document_type_id' => $validatedData['document_type_id'],
                'requester_id' => auth()->id(),
                'department_id' => $departmentIdForCode, // <-- ใช้ ID ของ "พะແນກຈັດຕັ້ງ-ສັງລວມ"
                'status' => $status,
                'total_amount' => 0,
                'parent_document_id' => $purchaseDocument->id,
            ]);
    
            // 6. บันทึก Items และคำนวณ Total Amount (ฉบับแก้ไข)
            $totalAmount = 0;
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $itemData) {
                    $description = $itemData['item_description'] ?? ($itemData['description'] ?? null);
                    if (empty($description)) continue;

                    $quantity = $itemData['quantity'] ?? 0;
                    $unitPrice = $itemData['unit_price'] ?? 0;
                    $totalPrice = $quantity * $unitPrice;

                    $paymentDocument->documentItems()->create([
                        'item_description' => $description,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ]);
                    $totalAmount += $totalPrice;
                }
            }
            
            $paymentDocument->total_amount = $totalAmount;
            $paymentDocument->save();
        
            // 7. บันทึก Attachments (ฉบับแก้ไข)
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments', 'public');
                    $paymentDocument->attachments()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                    ]);
                }
            }
    
            // 8. อัปเดตสถานะเอกสารจัดซื้อเดิม (ถูกต้อง)
            //if ($action === 'submit') {
                // เปลี่ยนสถานะเอกสารจัดซื้อเดิมเป็น "COMPLETED"
            $purchaseDocument->update(['status' => 'COMPLETED']);
            $purchaseDocument->documentLogs()->create([
                'user_id' => auth()->id(),
                'action' => 'Payment Request Created',
                'comment' => 'ສ້າງເອກະສານຂໍຖອນເງິນ (ID: ' . $paymentDocument->id . ') ຮຽບຮ້ອຍແລ້ວ'
            ]);
            //}
    
            DB::commit();

            // 9. ส่ง Notification (ถูกต้อง)
            if ($action === 'submit') {
                $secretaries = User::whereHas('role', function ($q) { $q->where('name', 'Dean_Secretary'); })->get();
                foreach ($secretaries as $secretary) {
                    $secretary->notify(new DocumentSubmitted($paymentDocument));
                }
            }
    
            // 10. Redirect (ถูกต้อง)
            $message = ($action === 'save_draft') ? 'ບັນທຶກເອກະສານຂໍຖອນເງິນສະບັບຮ່າງສໍາເລັດແລ້ວ' : 'ສ້າງແລະສົ່ງເອກະສານຂໍຖອນເງິນຮຽບຮ້ອຍແລ້ວ, ເອກະສານຈັດຊື້/ສ້ອມແປງນີ້ສໍາເລັດສົມບູນ';
            return redirect()->route('procurement.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage())->withInput();
        }
    }

    public function approvedHistory(Request $request)
    {
        // วิธีที่ถูกต้องคือ:
        // 1. ดึงเอกสาร "จัดซื้อ" (type 2) ทั้งหมดที่สถานะเป็น COMPLETED
        $completedPurchases = Document::where('document_type_id', 2)
                                  ->where('status', 'COMPLETED');

        // 2. ดึงเอกสาร "ขอถอนเงิน" (type 1) ที่ Procurement เป็นคนสร้าง และสถานะเป็น PAID
        $paidByProcurement = Document::where('requester_id', auth()->id())
                                 ->whereNotNull('parent_document_id')
                                 ->where('status', 'PAID');
    
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

        // 3. รวมผลลัพธ์
        $documents = $completedPurchases->union($paidByProcurement)
                                    ->with('requester.department', 'documentType')
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

        return view('procurement.history.approved', compact('documents', 'departments', 'statuses'));
    }

    /**
    * แสดงประวัติเอกสารที่ปฏิเสธโดยเลขาคนปัจจุบัน.
    */
    public function rejectedHistory(Request $request)
    {
        $documentIds = \App\Models\DocumentLog::where('user_id', auth()->id())
                                          ->where('action', 'Rejected by Procurement Staff')
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
        
        return view('procurement.history.rejected', compact('documents', 'departments', 'statuses'));
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
