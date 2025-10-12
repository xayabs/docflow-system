<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'ພາກວິຊາຄະນິດສາດແລະສະຖິຕິ'],
            ['name' => 'ພາກວິຊາຟີຊິກສາດ'],
            ['name' => 'ພາກວິຊາເຄມີສາດ'],
            ['name' => 'ພາກວິຊາຊີວະວິທະຍາ'],
            ['name' => 'ພາກວິຊາວິທະຍາສາດຄອມພິວເຕີ'],
            ['name' => 'ພະແນກຈັດຕັ້ງ-ສັງລວມ'],
            ['name' => 'ພະແນກວິຊາການ'],
            ['name' => 'ພະແນກແຜນການ-ການເງິນ'],
            ['name' => 'ພະແນກຄຸ້ມຄອງນັກສຶກສາ'],
            ['name' => 'ພະແນກຫຼັງປະລິນຍາຕີ'],
            ['name' => 'ພະແນກຄົ້ນຄວ້າ ແລະ ບໍລິການວິຊາການ'],
        ];

        foreach ($departments as $department) {
            if (!DB::table('departments')->where('name', $department['name'])->exists()) {
                DB::table('departments')->insert($department);
            }
        }
    }
}
