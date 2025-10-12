<?php

namespace Database\Seeders;

//use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            DocumentTypeSeeder::class,
            UserSeeder::class,
            // ສາມາດເພີ່ມ Seeder ອື່ນໆໄດ້ທີ່ນີ້ໃນອະນາຄົດ
        ]);
    }
}
