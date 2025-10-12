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
        Schema::create('documents', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED, PK, AI
            $table->string('document_code', 20)->unique()->nullable();
            $table->string('title');
            $table->string('status', 50);
            $table->decimal('total_amount', 15, 2);

            // Foreign Keys
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('document_type_id')->constrained('document_types');
            
            // Self-referencing Foreign Key for linking documents
            // onDelete('set null') means if the parent document is deleted, this field becomes NULL.
            $table->foreignId('parent_document_id')->nullable()->constrained('documents')->onDelete('set null');

            $table->text('rejected_reason')->nullable();
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};