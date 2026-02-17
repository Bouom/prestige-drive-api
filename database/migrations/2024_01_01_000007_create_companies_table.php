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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();

            // Company Info
            $table->string('legal_name', 255);
            $table->string('trade_name', 255)->nullable();
            $table->string('registration_number', 50)->unique();
            $table->string('vat_number', 50)->nullable();

            // Contact
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('website', 255)->nullable();

            // Address
            $table->text('address');
            $table->string('postal_code', 20);
            $table->string('city', 100);
            $table->string('country', 100)->default('France');

            // Legal Representative
            $table->string('representative_name', 255);
            $table->string('representative_position', 100)->nullable();

            // Financial
            $table->string('iban', 34)->nullable();
            $table->string('bic', 11)->nullable();
            $table->string('billing_email', 255)->nullable();

            // Fleet (denormalized)
            $table->unsignedInteger('total_drivers')->default(0);
            $table->unsignedInteger('active_drivers')->default(0);
            $table->unsignedInteger('total_vehicles')->default(0);

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');

            // Stripe
            $table->string('stripe_account_id', 255)->unique()->nullable();

            // Metadata
            $table->string('logo_url', 500)->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('registration_number');
            $table->index('email');
            $table->index(['is_active', 'deleted_at']);
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
