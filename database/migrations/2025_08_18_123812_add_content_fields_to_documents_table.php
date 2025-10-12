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
        Schema::table('documents', function (Blueprint $table) {
            $table->text('references')->nullable()->after('title'); // ບ່ອນອີງ
            $table->text('activity_description')->nullable()->after('references'); // ເນື້ອໃນກິດຈະກຳ
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['references', 'activity_description']);
        });
    }
};
