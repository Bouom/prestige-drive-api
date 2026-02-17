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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();

            // Ownership
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->unsignedBigInteger('current_driver_id')->nullable();

            // Vehicle Identification
            $table->foreignId('vehicle_model_id')->constrained('vehicle_models')->onDelete('restrict');
            $table->string('license_plate', 20)->unique();
            $table->string('vin', 17)->unique()->nullable();
            $table->string('registration_number', 50)->nullable();

            // Details
            $table->unsignedInteger('year');
            $table->string('color', 50)->nullable();
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric', 'hybrid']);
            $table->enum('transmission', ['manual', 'automatic']);

            // Capacity
            $table->unsignedInteger('passenger_capacity');
            $table->unsignedInteger('luggage_capacity')->nullable();

            // Features
            $table->json('features')->nullable();

            // Classification
            $table->enum('vehicle_class', ['economy', 'business', 'luxury', 'van']);

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->enum('current_status', ['available', 'on_ride', 'maintenance', 'out_of_service'])->default('available');

            // Maintenance
            $table->date('last_maintenance_at')->nullable();
            $table->date('next_maintenance_at')->nullable();
            $table->unsignedInteger('total_km')->default(0);

            // Insurance
            $table->string('insurance_company', 255)->nullable();
            $table->string('insurance_policy_number', 100)->nullable();
            $table->date('insurance_expires_at')->nullable();

            // Media
            $table->json('photos')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('company_id');
            $table->index('current_driver_id');
            $table->index('vehicle_model_id');
            $table->index('license_plate');
            $table->index(['current_status', 'is_active']);
            $table->index('vehicle_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
