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
        Schema::create('driver_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_profile_id')->constrained('driver_profiles')->onDelete('restrict');

            // Period
            $table->date('period_start');
            $table->date('period_end');

            // Rides Included
            $table->unsignedInteger('ride_count');
            $table->json('ride_ids');

            // Earnings Breakdown
            $table->decimal('gross_earnings', 10, 2);
            $table->decimal('platform_commission', 10, 2);
            $table->decimal('bonuses', 10, 2)->default(0.00);
            $table->decimal('deductions', 10, 2)->default(0.00);
            $table->decimal('net_payout', 10, 2);

            // Payment
            $table->enum('payment_method', ['bank_transfer', 'stripe_transfer', 'cash', 'check']);
            $table->string('payment_reference', 255)->nullable();

            // Status
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'cancelled'])->default('pending');

            // Timestamps
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamp('paid_at')->nullable();

            // Processing
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');

            // Metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('driver_profile_id');
            $table->index(['period_start', 'period_end']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_payouts');
    }
};
