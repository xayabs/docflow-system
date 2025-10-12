<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department; // Import Department Model ด้วย
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ดึงข้อมูล Roles ทั้งหมดมาเก็บไว้ใน Array (Key คือชื่อ Role)
        $roles = Role::pluck('id', 'name'); // ผลลัพธ์: ['System_Admin' => 1, 'Staff' => 2, ...]
        
        // 2. ดึงข้อมูล Departments ทั้งหมดมาเก็บไว้ใน Array
        $departments = Department::pluck('id', 'name');

        // --- สร้าง Admin ---
        if (isset($roles['System_Admin'])) {
            User::firstOrCreate(
                ['email' => 'admin-fns@nuol.edu.la'],
                [
                    'name' => 'Admin User',
                    'username' => 'admin',
                    'password' => Hash::make('12345678'),
                    'role_id' => $roles['System_Admin'],
                    'department_id' => null,
                ]
            );
        }

        // --- สร้าง Staff ---
        if (isset($roles['Staff'])) {
            // สร้าง Array ของข้อมูล Staff
            $staffData = [
                ['name' => 'Staff Maths', 'username' => 'maths', 'email' => 'maths@fns.nuol.edu.la', 'department_name' => 'ພາກວິຊາຄະນິດສາດແລະສະຖິຕິ'],
                ['name' => 'Staff Physics', 'username' => 'physic', 'email' => 'physic@fns.nuol.edu.la', 'department_name' => 'ພາກວິຊາຟີຊິກສາດ'],
                ['name' => 'Staff Chemistry', 'username' => 'chemy', 'email' => 'chemy@fns.nuol.edu.la', 'department_name' => 'ພາກວິຊາເຄມີສາດ'],
                ['name' => 'Staff Biology', 'username' => 'biotec', 'email' => 'biotec@fns.nuol.edu.la', 'department_name' => 'ພາກວິຊາຊີວະວິທະຍາ'],
                ['name' => 'Staff Computer Science', 'username' => 'coms', 'email' => 'coms@fns.nuol.edu.la', 'department_name' => 'ພາກວິຊາວິທະຍາສາດຄອມພິວເຕີ'],
                ['name' => 'Staff Management', 'username' => 'manag', 'email' => 'manag@fns.nuol.edu.la', 'department_name' => 'ພະແນກຈັດຕັ້ງ-ສັງລວມ'],
                ['name' => 'Staff Academic', 'username' => 'academ', 'email' => 'academ@fns.nuol.edu.la', 'department_name' => 'ພະແນກວິຊາການ'],
                ['name' => 'Staff Finance', 'username' => 'finan', 'email' => 'finan@fns.nuol.edu.la', 'department_name' => 'ພະແນກແຜນການ-ການເງິນ'],
                ['name' => 'Staff Student Affair', 'username' => 'stdaff', 'email' => 'stdaff@fns.nuol.edu.la', 'department_name' => 'ພະແນກຄຸ້ມຄອງນັກສຶກສາ'],
                ['name' => 'Staff Post Graduate', 'username' => 'pgrad', 'email' => 'pgrad@fns.nuol.edu.la', 'department_name' => 'ພະແນກຫຼັງປະລິນຍາຕີ'],
                ['name' => 'Staff Research/Acedemic Service', 'username' => 'raserv', 'email' => 'raserv@fns.nuol.edu.la', 'department_name' => 'ພະແນກຄົ້ນຄວ້າ ແລະ ບໍລິການວິຊາການ'],
            ];
            
            foreach ($staffData as $staff) {
                // ตรวจสอบว่า Department มีอยู่จริงหรือไม่
                if (isset($departments[$staff['department_name']])) {
                    User::firstOrCreate(
                        ['email' => $staff['email']],
                        [
                            'name' => $staff['name'],
                            'username' => $staff['username'],
                            'password' => Hash::make('12345678'),
                            'role_id' => $roles['Staff'],
                            'department_id' => $departments[$staff['department_name']],
                        ]
                    );
                }
            }
        }
        
        // --- สร้าง Role อื่นๆ (ที่ไม่มี Department) ---
        $otherRoles = [
            'Dean' => ['name' => 'Dean', 'username' => 'dean', 'email' => 'dean@fns.nuol.edu.la'],
            'Vice_Dean' => ['name' => 'Vice Dean', 'username' => 'vdean', 'email' => 'vdean@fns.nuol.edu.la'],
            'Head_of_Finance' => ['name' => 'Head of Finance', 'username' => 'hfinan', 'email' => 'hfinan@fns.nuol.edu.la'],
            'Finance_Preparer' => ['name' => 'Financial Preparer', 'username' => 'finprep', 'email' => 'finprep@fns.nuol.edu.la'],
            'Accountant' => ['name' => 'Accountant Staff', 'username' => 'accnt', 'email' => 'accnt@fns.nuol.edu.la'],
            'Cashier' => ['name' => 'Cashier Staff', 'username' => 'cash', 'email' => 'cash@fns.nuol.edu.la'],
            'Dean_Secretary' => ['name' => 'Dean Secretary', 'username' => 'dsecre', 'email' => 'dsecre@fns.nuol.edu.la'],
        ];

        foreach ($otherRoles as $roleName => $userData) {
            if (isset($roles[$roleName])) {
                User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'username' => $userData['username'],
                        'password' => Hash::make('12345678'),
                        'role_id' => $roles[$roleName],
                        'department_id' => null,
                    ]
                );
            }
        }

        if (isset($roles['Procurement_Staff']) && isset($departments['ພະແນກຈັດຕັ້ງ-ສັງລວມ'])) {
            User::firstOrCreate(
                ['email' => 'procmt@fns.nuol.edu.la'],
                [
                    'name' => 'Procurement Staff',
                    'username' => 'procmt',
                    'password' => Hash::make('12345678'),
                    'role_id' => $roles['Procurement_Staff'],
                    'department_id' => $departments['ພະແນກຈັດຕັ້ງ-ສັງລວມ'], // <-- กำหนด Department ที่ถูกต้อง
                ]
            );
        }
    }
}
