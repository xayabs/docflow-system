<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ขั้นตอนที่ 1: เพิ่มคอลัมน์ที่อนุญาตให้เป็น NULL ได้ก่อน
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        // ขั้นตอนที่ 2: อัปเดตข้อมูลเก่าให้มี username ที่ไม่ซ้ำกัน
        // เราจะใช้ email เป็น username ชั่วคราวไปก่อน
        \App\Models\User::whereNull('username')->each(function ($user) {
            $user->username = $user->email;
            $user->save();
        });

        // ขั้นตอนที่ 3: เปลี่ยนคอลัมน์ให้เป็น NOT NULL และ UNIQUE
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
