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
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('display_name', 100);
            $table->text('description')->nullable();

            // Applicability
            $table->json('applies_to');

            // Requirements
            $table->boolean('is_required')->default(false);
            $table->boolean('requires_expiry_date')->default(false);
            $table->boolean('requires_document_number')->default(false);
            $table->json('allowed_file_types')->nullable();
            $table->unsignedInteger('max_file_size_mb')->default(10);

            // Verification
            $table->boolean('requires_admin_approval')->default(true);

            // Alerts
            $table->unsignedInteger('expiry_alert_days_before')->default(30);

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
