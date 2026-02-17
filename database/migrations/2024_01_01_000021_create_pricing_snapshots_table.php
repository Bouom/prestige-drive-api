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
        Schema::create('pricing_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->unique()->constrained('rides')->onDelete('cascade');

            // Pricing Rule Version
            $table->foreignId('pricing_rule_id')->constrained('pricing_rules')->onDelete('restrict');
            $table->unsignedInteger('pricing_rule_version');

            // Calculation Breakdown
            $table->decimal('distance_km', 8, 2);
            $table->decimal('base_rate_per_km', 6, 2);
            $table->json('base_calculation');

            // Surcharges
            $table->json('surcharges')->nullable();

            // Discounts
            $table->json('discounts')->nullable();

            // Fees
            $table->decimal('return_fee', 10, 2)->default(0.00);
            $table->decimal('platform_fee_rate', 5, 2)->nullable();
            $table->decimal('platform_fee_amount', 10, 2)->nullable();

            // Totals
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total_surcharges', 10, 2)->default(0.00);
            $table->decimal('total_discounts', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(20.00);
            $table->decimal('tax_amount', 10, 2)->nullable();
            $table->decimal('final_total', 10, 2);

            // Metadata
            $table->timestamp('calculated_at')->useCurrent();
            $table->json('calculation_metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_snapshots');
    }
};
