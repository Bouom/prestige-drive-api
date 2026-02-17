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
            $table->id();
            $table->char('uuid', 36)->unique();

            // Polymorphic Relationship
            $table->string('documentable_type', 100);
            $table->unsignedBigInteger('documentable_id');

            // Document Classification
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('restrict');

            // File Details
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->unsignedInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('file_hash', 64)->nullable();

            // Document Metadata
            $table->string('document_number', 100)->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();

            // Verification
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();

            // Alerts
            $table->timestamp('expiry_notification_sent_at')->nullable();

            // Version Control
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('replaces_document_id')->nullable()->constrained('documents')->onDelete('set null');

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['documentable_type', 'documentable_id']);
            $table->index('document_type_id');
            $table->index('status');
            $table->index('expires_at');
            $table->index('file_hash');
        });

        // Add fulltext index
        DB::statement('ALTER TABLE documents ADD FULLTEXT INDEX idx_document_number (document_number)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
