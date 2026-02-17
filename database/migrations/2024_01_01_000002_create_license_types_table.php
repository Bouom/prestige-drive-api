<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();

            // Requirements
            $table->unsignedInteger('minimum_age')->nullable();
            $table->boolean('requires_professional_card')->default(false);

            // Vehicle Restrictions
            $table->unsignedInteger('max_passenger_capacity')->nullable();
            $table->unsignedInteger('max_vehicle_weight')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_types');
    }
};
