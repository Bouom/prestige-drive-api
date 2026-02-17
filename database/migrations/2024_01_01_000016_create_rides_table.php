<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('booking_reference', 20)->unique();

            // Parties
            $table->foreignId('customer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('driver_id')->nullable()->constrained('driver_profiles')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');

            // Trip Details
            $table->foreignId('trip_type_id')->constrained('trip_types')->onDelete('restrict');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');

            // Route
            $table->text('pickup_address');
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 11, 8);
            $table->text('dropoff_address');
            $table->decimal('dropoff_latitude', 10, 8);
            $table->decimal('dropoff_longitude', 11, 8);

            // Timing
            $table->timestamp('scheduled_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Distance & Duration
            $table->decimal('estimated_distance_km', 8, 2);
            $table->unsignedInteger('estimated_duration_min');
            $table->decimal('actual_distance_km', 8, 2)->nullable();
            $table->unsignedInteger('actual_duration_min')->nullable();

            // Passengers
            $table->unsignedInteger('passenger_count')->default(1);
            $table->boolean('has_luggage')->default(false);
            $table->boolean('requires_child_seat')->default(false);

            // Round Trip
            $table->boolean('is_round_trip')->default(false);
            $table->timestamp('return_scheduled_at')->nullable();

            // Pricing Snapshot
            $table->decimal('base_price', 10, 2);
            $table->decimal('return_fee', 10, 2)->default(0.00);
            $table->decimal('surcharge', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('discount_code', 50)->nullable();
            $table->decimal('total_price', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0.00);
            $table->decimal('taxes', 10, 2)->default(0.00);
            $table->decimal('final_price', 10, 2);

            // Driver Earnings
            $table->decimal('driver_earnings', 10, 2)->nullable();

            // Status
            $table->enum('status', [
                'pending', 'quote', 'confirmed', 'assigned', 'accepted',
                'on_the_way', 'arrived', 'in_progress', 'completed',
                'cancelled_by_customer', 'cancelled_by_driver', 'cancelled_by_admin', 'no_show',
            ])->default('pending');

            $table->enum('payment_status', ['pending', 'authorized', 'paid', 'refunded', 'failed'])->default('pending');

            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('driver_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Confirmation
            $table->boolean('requires_date_confirmation')->default(false);
            $table->timestamp('date_confirmed_at')->nullable();

            // Media
            $table->string('pickup_photo_url', 500)->nullable();
            $table->string('dropoff_photo_url', 500)->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('booking_reference');
            $table->index('customer_id');
            $table->index('driver_id');
            $table->index('company_id');
            $table->index(['status', 'payment_status']);
            $table->index('scheduled_at');
            $table->index(['pickup_latitude', 'pickup_longitude']);
            $table->index('created_at');
        });

        // Add fulltext index for addresses
        DB::statement('ALTER TABLE rides ADD FULLTEXT INDEX idx_addresses (pickup_address, dropoff_address)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
