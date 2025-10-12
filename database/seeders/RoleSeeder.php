<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            // ບົດບາດຕາມ Workflow
            ['name' => 'Staff'],                // ພະນັກງານ/ຜູ້ສະເໜີ
            ['name' => 'Dean_Secretary'],        // ເລຂາຄະນະ
            ['name' => 'Finance_Preparer'],      // ຝ່າຍກະກຽມເອກະສານຈົດຈ່າຍ
            ['name' => 'Accountant'],            // ນາຍບັນຊີ
            ['name' => 'Procurement_Staff'],     // ພະແນກຈັດຕັ້ງ
            ['name' => 'Vice_Dean'],             // ຮອງຄະນະບໍດີ
            ['name' => 'Head_of_Finance'],       // ຫົວໜ້າການເງິນ
            ['name' => 'Cashier'],               // ຄັງເງິນສົດ
            ['name' => 'Dean'],                  // ຄະນະບໍດີ

            // ບົດບາດພິເສດ
            ['name' => 'System_Admin'],          // ຜູ້ບໍລິຫານລະບົບ
        ];

        foreach ($roles as $role) {
            // ກວດສອບກ່ອນວ່າ role ນີ້ມີຢູ່ແລ້ວຫຼືບໍ່
            if (!DB::table('roles')->where('name', $role['name'])->exists()) {
                DB::table('roles')->insert($role);
            }
        }
    }
}
