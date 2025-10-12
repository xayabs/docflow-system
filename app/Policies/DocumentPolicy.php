<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role->name, [
            'System_Admin', 
            'Staff',
            'Dean_Secretary',
            'Finance_Preparer',
            'Accountant',
            'Vice_Dean',
            'Head_of_Finance',
            'Dean',
            'Cashier',
            'Procurement_Staff',
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Document $document): bool
    {
        $userRole = $user->role->name;

        // --- เงื่อนไขพิเศษ ---

        // 1. Admin สามารถดูได้ทุกอย่าง
        if ($user->role->name === 'System_Admin') {
            return true;
        }

        // 2. ผู้สร้าง (Requester) สามารถดูเอกสารของตัวเองได้เสมอ
        if ($user->id === $document->requester_id) {
            return true;
        }
    
        // --- เงื่อนไขสำหรับผู้ร่วมภาคส่วน ---

        // 3. ถ้าผู้ใช้เป็น Staff, ให้ตรวจสอบว่าเป็นเอกสารในภาคส่วนของตนเองหรือไม่
        /*
        if ($user->role->name === 'Staff') {
            return $user->department_id === $document->department_id;
        }*/

        if ($user->role->name === 'Staff') {
        // เงื่อนไข 1: เป็นเอกสารในภาคส่วนของตัวเอง (เหมือนเดิม)
            if ($user->department_id === $document->department_id) {
                return true;
            }

            // เงื่อนไข 2 (ใหม่): ถ้าผู้ใช้สังกัดแผนกจัดตั้ง, 
            // และเอกสารกำลังอยู่ในกระบวนการของฝ่ายจัดซื้อ, ให้ดูได้
            if ($user->department_id == 6) { // สมมติว่า 6 คือ ID ของแผนกจัดตั้ง
                if (in_array($document->status, [
                    'PENDING_PROCUREMENT_EVALUATION',
                    'PROCUREMENT_IN_PROGRESS',
                    'PURCHASE_COMPLETE_PENDING_PAYMENT',
                ])) {
                    return true;
                }
            }
        }
    
        // --- เงื่อนไขสำหรับผู้ตรวจสอบ (Approvers) ---

        // 4. ตรวจสอบว่าผู้ใช้มีสิทธิ์ดูเอกสารในสถานะปัจจุบันหรือไม่
        $permissions = [
            'PENDING_SECRETARY_REVIEW' => ['Dean_Secretary'],
            'PENDING_FINANCE_PREPARER_REVIEW' => ['Finance_Preparer'],
            'PENDING_ACCOUNTANT_BUDGET_CHECK' => ['Accountant'],
            'PENDING_VICE_DEAN_APPROVAL' => ['Vice_Dean'],
            'PENDING_FINANCE_HEAD_APPROVAL' => ['Head_of_Finance'],
            'PENDING_DEAN_FINAL_APPROVAL' => ['Dean'],
            'PENDING_DEAN_APPROVAL' => ['Dean', 'Vice_Dean'],
            'READY_FOR_PAYMENT' => ['Cashier'],
            'PENDING_PROCUREMENT_EVALUATION' => ['Procurement_Staff'],
            'PROCUREMENT_IN_PROGRESS' => ['Procurement_Staff'],
            'PURCHASE_COMPLETE_PENDING_PAYMENT' => ['Procurement_Staff'],
        ];

        if (array_key_exists($document->status, $permissions)) {
            return in_array($user->role->name, $permissions[$document->status]);
        }
    
        // --- เงื่อนไขสุดท้าย ---
    
        // 5. ถ้าเอกสารจบกระบวนการแล้ว (PAID, REJECTED, COMPLETED)
        // อนุญาตให้ผู้ที่ "สังกัดภาคส่วนเดียวกับเอกสาร" สามารถกลับมาดูได้
        if (in_array($document->status, ['PAID', 'REJECTED', 'COMPLETED'])) {
            if ($user->department_id === $document->department_id) {
                return true;
            }
        }
    
        // ถ้าไม่เข้าเงื่อนไขใดๆ เลย, ไม่อนุญาต
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        // อนุญาตให้อัปเดตได้ ก็ต่อเมื่อ:
        // 1. ผู้ใช้เป็นคนสร้างเอกสาร (Requester)
        // 2. และ สถานะของเอกสารเป็น DRAFT หรือ REJECTED
        return $user->id === $document->requester_id && 
           in_array($document->status, ['DRAFT', 'REJECTED']);
    }

    /**
    * (Optional but Recommended) สร้าง Scope เพื่อกรองข้อมูล
    */
    public function scope(User $user, $query)
    {
        // ถ้าเป็น Admin, ไม่ต้องกรองอะไรเลย (เห็นทุกอย่าง)
        if ($user->role->name === 'System_Admin') {
            return $query;
    }

    // ถ้าเป็น Staff, ให้กรองเฉพาะเอกสารในภาคส่วนของตัวเอง
    if ($user->role->name === 'Staff') {
        return $query->where('department_id', $user->department_id);
    }
        // ถ้าเป็น Role อื่นๆ ที่ไม่ควรเข้าหน้านี้, ให้คืนค่า Query ที่ไม่เจออะไรเลย
        return $query->where('id', -1); // เงื่อนไขที่เป็น false เสมอ
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return false;
    }
}
