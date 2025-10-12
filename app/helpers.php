<?php

if (!function_exists('translateStatus')) {
    function translateStatus($status)
    {
        $translations = [
            'DRAFT' => 'ສະບັບຮ່າງ',
            'PENDING_SECRETARY_REVIEW' => 'ລໍຖ້າເລຂາກວດສອບ',
            'PENDING_FINANCE_PREPARER_REVIEW' => 'ລໍຖ້າຝ່າຍການເງິນກວດສອບ',
            'PENDING_ACCOUNTANT_BUDGET_CHECK' => 'ລໍຖ້ານາຍບັນຊີກວດສອບງົບ',
            'PENDING_VICE_DEAN_APPROVAL' => 'ລໍຖ້າຮອງຄະນະບໍດີອະນຸມັດ',
            'PENDING_ACCOUNTANT_POSTING' => 'ລໍຖ້ານາຍບັນຊີລົງບັນຊີ',
            'PENDING_FINANCE_HEAD_APPROVAL' => 'ລໍຖ້າຫົວໜ້າການເງິນອະນຸມັດ',
            'PENDING_DEAN_FINAL_APPROVAL' => 'ລໍຖ້າຄະນະບໍດີອະນຸມັດຈ່າຍ',
            'READY_FOR_PAYMENT' => 'ພ້ອມຈ່າຍ',
            'PAID' => 'ຈ່າຍເງິນແລ້ວ',
            'REJECTED' => 'ຖືກປະຕິເສດ',
            // ເພີ່ມສະຖານະຂອງ Workflow ທີ 2 ຢູ່ທີ່ນີ້
            'PENDING_DEAN_APPROVAL' => 'ລໍຖ້າຄະນະບໍດີອະນຸມັດຫຼັກການ',
            'PENDING_PROCUREMENT_EVALUATION' => 'ລໍຖ້າພະແນກຈັດຕັ້ງປະເມີນລາຄາ',
            'PROCUREMENT_IN_PROGRESS' => 'ກຳລັງຈັດຊື້',
            'PURCHASE_COMPLETE_PENDING_PAYMENT' => 'ຈັດຊື້/ສ້ອມແປງສຳເລັດ, ລໍຖ້າຖອນເງິນ',
            'COMPLETED' => 'ສຳເລັດສົມບູນ',
        ];

        return $translations[$status] ?? $status;
    }
}

if (!function_exists('get_all_statuses_translation')) {
    /**
     * คืนค่า Array ของสถานะทั้งหมดพร้อมคำแปล.
     */
    function get_all_statuses_translation()
    {
        return [
            'DRAFT' => 'ສະບັບຮ່າງ',
            'PENDING_SECRETARY_REVIEW' => 'ລໍຖ້າເລຂາກວດສອບ',
            'PENDING_FINANCE_PREPARER_REVIEW' => 'ລໍຖ້າຝ່າຍການເງິນກວດສອບ',
            'PENDING_ACCOUNTANT_BUDGET_CHECK' => 'ລໍຖ້ານາຍບັນຊີກວດສອບງົບ',
            'PENDING_ACCOUNTANT_POSTING' => 'ລໍຖ້ານາຍບັນຊີລົງບັນຊີ',
            'PENDING_VICE_DEAN_APPROVAL' => 'ລໍຖ້າຮອງຄະນະບໍດີອະນຸມັດ',
            'PENDING_FINANCE_HEAD_APPROVAL' => 'ລໍຖ້າຫົວໜ້າການເງິນອະນຸມັດ',
            'PENDING_DEAN_FINAL_APPROVAL' => 'ລໍຖ້າຄະນະບໍດີອະນຸມັດຈ່າຍ',
            'READY_FOR_PAYMENT' => 'ພ້ອມຈ່າຍ',
            'PAID' => 'ຈ່າຍເງິນແລ້ວ',
            'REJECTED' => 'ຖືກປະຕິເສດ',
            'PENDING_DEAN_APPROVAL' => 'ລໍຖ້າຄະນະບໍດີອະນຸມັດຫຼັກການ',
            'PENDING_PROCUREMENT_EVALUATION' => 'ລໍຖ້າພະແນກຈັດຕັ້ງປະເມີນລາຄາ',
            'PROCUREMENT_IN_PROGRESS' => 'ກຳລັງຈັດຊື້',
            'PURCHASE_COMPLETE_PENDING_PAYMENT' => 'ຈັດຊື້ສຳເລັດ, ລໍຖ້າຖອນເງິນ',
            'COMPLETED' => 'ສຳເລັດສົມບູນ',
        ];
    }
}

if (!function_exists('getStatusColorClass')) {
    function getStatusColorClass($status)
    {
        switch ($status) {
            case 'PAID':
            case 'COMPLETED':
                return 'bg-green-100 text-green-800'; // ສີຂຽວ
            case 'REJECTED':
                return 'bg-red-100 text-red-800'; // ສີແດງ
            case 'READY_FOR_PAYMENT':
                return 'bg-blue-100 text-blue-800'; // ສີຟ້າ
            case 'DRAFT':
                return 'bg-gray-100 text-gray-800'; // ສີເທົາ
            default:
                return 'bg-yellow-100 text-yellow-800'; // ສີເຫຼືອງ (ສຳລັບສະຖານະ Pending ທັງໝົດ)
        }
    }
}

if (!function_exists('formatLaoDate')) {
    function formatLaoDate($date) {
        $carbonDate = \Carbon\Carbon::parse($date);
        $laoMonths = [
            1=>'ມັງກອນ', 2=>'ກຸມພາ', 3=>'ມີນາ', 4=>'ເມສາ', 5=>'ພຶດສະພາ', 6=>'ມິຖຸນາ',
            7=>'ກໍລະກົດ', 8=>'ສິງຫາ', 9=>'ກັນຍາ', 10=>'ຕຸລາ', 11=>'ພະຈິກ', 12=>'ທັນວາ'
        ];
        return "ວັນທີ " . $carbonDate->day . " " . $laoMonths[$carbonDate->month] . " " . $carbonDate->year;
    }
}

if (!function_exists('getDepartmentAbbreviation')) {
    function getDepartmentAbbreviation($departmentName) {
        $abbreviations = [
            'ພາກວິຊາຄະນິດສາດແລະສະຖິຕິ' => 'ຄສ', 'ພາກວິຊາຟິຊິກສາດ' => 'ຟຊ',
            'ພາກວິຊາເຄມີສາດ' => 'ຄມ', 'ພາກວິຊາຊີວະວິທະຍາ' => 'ຊວ',
            'ພາກວິຊາວິທະຍາສາດຄອມພິວເຕີ' => 'ຄຕ', 'ພະແນກຈັດຕັ້ງ-ສັງລວມ' => 'ຈຕ',
            'ພະແນກວິຊາການ' => 'ວກ', 'ພະແນກແຜນການ-ການເງິນ' => 'ກງ',
            'ພະແນກຄຸ້ມຄອງນັກສຶກສາ' => 'ຄນ', 'ພະແນກການສຶກສາຫຼັງປະລິນຍາຕີ' => 'ຫຼຕ',
            'ພະແນກຄົ້ນຄວ້າ ແລະ ບໍລິການວິຊາການ' => 'ຄບ'
        ];
        return $abbreviations[$departmentName] ?? '??';
    }
}

if (!function_exists('getActionVerbFromTitle')) {
    function getActionVerbFromTitle($title) {
        if (str_contains($title, 'ຈັດຊື້')) {
            return 'ຈັດຊື້';
        }
        if (str_contains($title, 'ສ້ອມແປງ')) {
            return 'ສ້ອມແປງ';
        }
        // ค่าเริ่มต้น ถ้าไม่เจอทั้งสองคำ
        return 'ດຳເນີນການ'; 
    }
}

if (!function_exists('getDepartmentType')) {
    function getDepartmentType($departmentName) {
        if (str_starts_with($departmentName, 'ພາກວິຊາ')) {
            return 'ພາກວິຊາ';
        }
        if (str_starts_with($departmentName, 'ພະແນກ')) {
            return 'ພະແນກ';
        }
        // ค่าเริ่มต้น
        return 'ພາກສ່ວນ';
    }
}

if (!function_exists('getShowUrlForRole')) {
    function getShowUrlForRole($roleName, $documentId)
    {
        $routePrefix = strtolower(str_replace('_', '', $roleName));
        
        // จัดการกรณีพิเศษ
        if ($roleName === 'Finance_Preparer') $routePrefix = 'finance.preparer';
        if ($roleName === 'Head_of_Finance') $routePrefix = 'headfinance';
        if ($roleName === 'Vice_Dean') $routePrefix = 'vicedean';
        if ($roleName === 'Dean_Secretary') $routePrefix = 'secretary';
        if ($roleName === 'System_Admin') return route('admin.users.index'); 
        if ($roleName === 'Procurement_Staff') $routePrefix = 'procurement';
        if ($roleName === 'Staff') $routePrefix = 'staff';
        if ($roleName === 'Accountant') $routePrefix = 'accountant';
        if ($roleName === 'Dean') $routePrefix = 'dean';
        if ($roleName === 'Cashier') $routePrefix = 'cashier';
        
        $routeName = $routePrefix . '.documents.show';

        if (\Illuminate\Support\Facades\Route::has($routeName)) {
            return route($routeName, $documentId);
        }
        return route('dashboard'); // Fallback
    }
}


if (!function_exists('getRoleNameFromStatus')) {
    /**
     * แปลงค่า Status ให้กลายเป็นชื่อ Role ของผู้รับผิดชอบ.
     */
    function getRoleNameFromStatus($status)
    {
        $statusToRoleMap = [
            'PENDING_SECRETARY_REVIEW' => 'Dean_Secretary',
            'PENDING_FINANCE_PREPARER_REVIEW' => 'Finance_Preparer',
            'PENDING_ACCOUNTANT_BUDGET_CHECK' => 'Accountant',
            'PENDING_ACCOUNTANT_POSTING' => 'Accountant',
            'PENDING_VICE_DEAN_APPROVAL' => 'Vice_Dean',
            'PENDING_FINANCE_HEAD_APPROVAL' => 'Head_of_Finance',
            'PENDING_DEAN_FINAL_APPROVAL' => 'Dean',
            'PENDING_DEAN_APPROVAL' => 'Dean',
            'READY_FOR_PAYMENT' => 'Cashier',
            'PENDING_PROCUREMENT_EVALUATION' => 'Procurement_Staff',
        ];

        return $statusToRoleMap[$status] ?? null;
    }
}


if (!function_exists('get_available_statuses')) {
    function get_available_statuses() {
        $all = get_all_statuses_translation();
        unset($all['DRAFT']); // เอา DRAFT ออก
        return $all;
    }
}
