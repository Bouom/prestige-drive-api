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
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->nullable()->constrained('rides')->onDelete('set null');
            $table->foreignId('driver_id')->constrained('driver_profiles')->onDelete('restrict');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');

            // Violation Details
            $table->enum('violation_type', ['speeding', 'parking', 'red_light', 'other']);
            $table->timestamp('violation_date');
            $table->text('location')->nullable();

            // Fine
            $table->decimal('fine_amount', 10, 2);
            $table->string('currency', 3)->default('EUR');

            // Responsibility
            $table->enum('responsible_party', ['driver', 'customer', 'company', 'pending'])->default('pending');

            // Evidence
            $table->string('ticket_number', 100)->unique()->nullable();
            $table->string('ticket_document_url', 500)->nullable();

            // Payment
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'deducted_from_earnings'])->nullable();

            // Dispute
            $table->boolean('is_disputed')->default(false);
            $table->text('dispute_reason')->nullable();
            $table->enum('dispute_status', ['pending', 'accepted', 'rejected'])->nullable();
            $table->timestamp('dispute_resolved_at')->nullable();

            // Admin Notes
            $table->text('admin_notes')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('ride_id');
            $table->index('driver_id');
            $table->index('vehicle_id');
            $table->index('responsible_party');
            $table->index('is_paid');
            $table->index('ticket_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
