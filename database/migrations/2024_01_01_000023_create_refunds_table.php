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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('restrict');

            // Refund Details
            $table->string('refund_id', 100)->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('reason', ['requested_by_customer', 'duplicate', 'fraudulent', 'cancelled_ride']);
            $table->text('reason_details')->nullable();

            // Status
            $table->enum('status', ['pending', 'succeeded', 'failed', 'cancelled'])->default('pending');

            // Processing
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');

            // Error
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('payment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
