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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('version')->default(1);

            // Applicability
            $table->foreignId('trip_type_id')->nullable()->constrained('trip_types')->onDelete('set null');
            $table->json('applicable_countries')->nullable();

            // Base Pricing
            $table->decimal('base_rate_per_km', 6, 2);
            $table->decimal('minimum_fare', 10, 2);
            $table->decimal('base_fee', 10, 2)->default(0.00);

            // Distance Tiers
            $table->json('distance_tiers')->nullable();

            // Time-Based Pricing
            $table->json('time_multipliers')->nullable();

            // Demand-Based Pricing
            $table->boolean('surge_enabled')->default(false);
            $table->decimal('max_surge_multiplier', 4, 2)->default(2.00);

            // Return Fee
            $table->decimal('return_fee_base', 10, 2)->default(0.00);
            $table->decimal('return_fee_per_km', 6, 2)->default(0.00);

            // Platform Commission
            $table->decimal('platform_commission_rate', 5, 2);

            // Validity
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['name', 'version']);
            $table->index('trip_type_id');
            $table->index(['is_active', 'valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
