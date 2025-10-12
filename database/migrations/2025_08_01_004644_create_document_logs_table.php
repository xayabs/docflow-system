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
        Schema::create('document_logs', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED, PK, AI

            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // The user who performed the action

            $table->string('action'); // e.g., 'SUBMITTED', 'APPROVED_BY_DEAN', 'REJECTED'
            $table->text('comment')->nullable();
            
            // A log record should not be updated, so we only need created_at.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_logs');
    }
};