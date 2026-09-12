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

        // --- 1. ເງື່ອນໄຂພິເສດ (ກວດສອບກ່ອນສະເໝີ) ---

        // Admin ສາມາດເບິ່ງໄດ້ທຸກຢ່າງ
        if ($userRole === 'System_Admin') {
            return true;
        }

        // ຜູ້ສ້າງເອກະສານ (Requester) ສາມາດເບິ່ງເອກະສານຂອງຕົນເອງໄດ້ສະເໝີ
        if ($user->id === $document->requester_id) {
            return true;
        }

        // ===== ເພີ່ມຈຸດນີ້: ໃຫ້ຜູ້ບໍລິຫານສາມາດເຂົ້າເບິ່ງເອກະສານທັງໝົດໄດ້ (ຍົກເວັ້ນສະບັບຮ່າງ DRAFT) =====
        if (in_array($userRole, ['Dean', 'Vice_Dean', 'Head_of_Finance'])) {
            return $document->status !== 'DRAFT';
        }
        // ===================================================================================


        // --- 2. ເງື່ອນໄຂສຳລັບພະນັກງານປົກກະຕິ (Staff) ---
        if ($userRole === 'Staff') {
            // ເງື່ອນໄຂ 2.1: ເປັນເອກະສານໃນພາກສ່ວນຂອງຕົນເອງ
            if ($user->department_id === $document->department_id) {
                return true;
            }

            // ເງື່ອນໄຂ 2.2: ຫາກສັງກັດພະແນກຈັດຕັ້ງ (ID = 6) ແລະ ເອກະສານກຳລັງຢູ່ໃນຂັ້ນຕອນການຈັດຊື້
            /*if ($user->department_id == 6) {
                // ກ. ເບິ່ງເອກະສານຈັດຊື້ທີ່ກຳລັງດຳເນີນການ
                if (in_array($document->status, [
                    'PENDING_PROCUREMENT_EVALUATION',
                    'PROCUREMENT_IN_PROGRESS',
                    'PURCHASE_COMPLETE_PENDING_PAYMENT',
                ])) {
                    return true;
                }*/
            if ($user->department_id == 6) {
                // อนุญาตให้ดูเอกสาร "จัดซื้อ" (ID=2) ได้ทั้งหมด ไม่ว่าจะสถานะใด
                // (เพราะเมื่อเป็นเอกสารจัดซื้อ, มันจะต้องเกี่ยวพันกับแผนกจัดตั้งเสมอ)
                if ($document->document_type_id == 2) {
                    return true;
                }
                // ຂ. (ເພີ່ມໃໝ່) ເບິ່ງເອກະສານ "ຂໍຖອນເງິນ" ທີ່ສ້າງໂດຍຝ່າຍຈັດຊື້ ໄດ້ທຸກສະຖານະ
                /*if ($document->document_type_id == 1 && $document->parent_document_id !== null && $document->department_id == 6) {
                    return true; 
                }*/
                // อนุญาตให้ดูเอกสาร "ขอถอนเงิน" (ID=1) ที่สร้างต่อเนื่องมาจากการจัดซื้อ
                if ($document->document_type_id == 1 && $document->parent_document_id !== null) {
                    return true; 
                }
            }
        }


        // --- 3. ເງື່ອນໄຂສຳລັບຜູ້ກວດສອບທີ່ກຳລັງດຳເນີນການ (Active Approvers) ---
        $permissions = [
            'PENDING_SECRETARY_REVIEW' => ['Dean_Secretary'],
            'PENDING_FINANCE_PREPARER_REVIEW' => ['Finance_Preparer'],
            'PENDING_ACCOUNTANT_BUDGET_CHECK' => ['Accountant'],
            'PENDING_VICE_DEAN_APPROVAL' => ['Vice_Dean'],
            'PENDING_ACCOUNTANT_POSTING' => ['Accountant'],
            'PENDING_FINANCE_HEAD_APPROVAL' => ['Head_of_Finance'],
            'PENDING_FINANCE_HEAD_VERIFICATION' => ['Head_of_Finance'],
            'PENDING_DEAN_FINAL_APPROVAL' => ['Dean'],
            'PENDING_DEAN_APPROVAL' => ['Dean', 'Vice_Dean'],
            'READY_FOR_PAYMENT' => ['Cashier'],
            'PENDING_PROCUREMENT_EVALUATION' => ['Procurement_Staff'],
            'PROCUREMENT_IN_PROGRESS' => ['Procurement_Staff'],
            'PURCHASE_COMPLETE_PENDING_PAYMENT' => ['Procurement_Staff'],
        ];

        if (array_key_exists($document->status, $permissions)) {
            return in_array($userRole, $permissions[$document->status]);
        }


        // --- 4. ເງື່ອນໄຂສຸດທ້າຍ: ສຳລັບເອກະສານທີ່ຈົບຂະບວນການແລ້ວ ---
        // ຫາກເອກະສານຈົບຂະບວນການແລ້ວ (PAID, REJECTED, COMPLETED)
        if (in_array($document->status, ['PAID', 'REJECTED', 'COMPLETED'])) {
            // ອະນຸຍາດໃຫ້ ຜູ້ຮ່ວມພາກສ່ວນ, ນາຍບັນຊີ, ແລະ ຄັງເງິນສົດ ສາມາດກັບມາເບິ່ງຄືນໄດ້ (ເພື່ອກວດສອບປະຫວັດ)
            if ($user->department_id === $document->department_id || in_array($userRole, ['Accountant', 'Cashier'])) {
                return true;
            }
        }

        // ຫາກບໍ່ເຂົ້າເງື່ອນໄຂໃດເລີຍ, ແມ່ນບໍ່ມີສິດເຂົ້າເບິ່ງ
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
