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
        Schema::create('ride_waypoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained('rides')->onDelete('cascade');

            // Order
            $table->unsignedInteger('sequence');

            // Location
            $table->text('address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // Type
            $table->enum('waypoint_type', ['pickup', 'stop', 'dropoff']);

            // Timing
            $table->timestamp('estimated_arrival')->nullable();
            $table->timestamp('actual_arrival')->nullable();
            $table->unsignedInteger('wait_time_minutes')->default(0);

            // Status
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->string('contact_name', 255)->nullable();
            $table->string('contact_phone', 20)->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['ride_id', 'sequence']);
            $table->index('ride_id');
            $table->index('waypoint_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_waypoints');
    }
};
