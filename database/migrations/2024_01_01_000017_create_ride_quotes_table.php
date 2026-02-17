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
        Schema::create('ride_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Route
            $table->text('pickup_address');
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 11, 8);
            $table->text('dropoff_address');
            $table->decimal('dropoff_latitude', 10, 8);
            $table->decimal('dropoff_longitude', 11, 8);

            // Trip Details
            $table->foreignId('trip_type_id')->constrained('trip_types')->onDelete('restrict');
            $table->enum('trip_purpose', ['personal', 'professional', 'vulnerable'])->default('personal');
            $table->boolean('is_round_trip')->default(false);
            $table->unsignedInteger('passenger_count')->default(1);

            // Vehicle Info
            $table->foreignId('vehicle_brand_id')->nullable()->constrained('vehicle_brands')->onDelete('set null');
            $table->foreignId('vehicle_model_id')->nullable()->constrained('vehicle_models')->onDelete('set null');

            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('return_scheduled_at')->nullable();

            // Calculated Pricing
            $table->decimal('estimated_distance_km', 8, 2);
            $table->unsignedInteger('estimated_duration_min');
            $table->decimal('estimated_price', 10, 2);

            // Conversion
            $table->unsignedBigInteger('converted_to_ride_id')->nullable();

            // Session
            $table->string('session_id', 100)->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('user_id');
            $table->index('session_id');
            $table->index('created_at');
            $table->index('converted_to_ride_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_quotes');
    }
};
