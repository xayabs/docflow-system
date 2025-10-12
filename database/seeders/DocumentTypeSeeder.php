<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ใช้ firstOrCreate เพื่อป้องกันข้อมูลซ้ำซ้อน
        DocumentType::firstOrCreate(
            ['id' => 1], // เงื่อนไขในการค้นหา
            ['name' => 'ຂໍຖອນເງິນ'] // ข้อมูลที่จะสร้างถ้าไม่เจอ
        );

        DocumentType::firstOrCreate(
            ['id' => 2],
            ['name' => 'ຂໍສະເໜີຈັດຊື້/ສ້ອມແປງ']
        );
        
        // คุณสามารถเพิ่มประเภทอื่นๆ ได้ที่นี่
    }
}
