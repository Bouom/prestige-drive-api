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
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->foreignId('license_type_id')->constrained('license_types')->onDelete('restrict');

            // Professional Info
            $table->string('license_number', 50)->unique();
            $table->date('license_issued_at')->nullable();
            $table->date('license_expires_at')->nullable();
            $table->string('professional_card_number', 50)->nullable();
            $table->unsignedInteger('years_experience')->default(0);

            // Employment
            $table->enum('employment_type', ['independent', 'company_employed']);
            $table->date('joined_platform_at')->nullable();

            // Availability
            $table->boolean('is_available')->default(false);
            $table->boolean('is_on_ride')->default(false);
            $table->boolean('accepts_shared_rides')->default(false);
            $table->unsignedInteger('max_passengers')->default(4)->nullable();

            // Location (real-time)
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('location_updated_at')->nullable();
            $table->unsignedInteger('heading')->nullable();

            // Performance Metrics (denormalized)
            $table->unsignedInteger('total_rides')->default(0);
            $table->unsignedInteger('completed_rides')->default(0);
            $table->unsignedInteger('cancelled_rides')->default(0);
            $table->decimal('acceptance_rate', 5, 2)->default(100.00);
            $table->decimal('cancellation_rate', 5, 2)->default(0.00);
            $table->unsignedInteger('average_response_time')->nullable();

            // Earnings
            $table->decimal('total_earnings', 10, 2)->default(0.00);
            $table->decimal('pending_payout', 10, 2)->default(0.00);

            // Verification Status
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');

            // Banking
            $table->string('iban', 34)->nullable();
            $table->string('bic', 11)->nullable();
            $table->string('bank_account_holder', 255)->nullable();

            // Metadata
            $table->text('bio')->nullable();
            $table->json('languages')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('company_id');
            $table->index('vehicle_id');
            $table->index(['is_available', 'is_on_ride', 'deleted_at']);
            $table->index(['current_latitude', 'current_longitude']);
            $table->index('license_expires_at');
            $table->index(['is_verified', 'verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
