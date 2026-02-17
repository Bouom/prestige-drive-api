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
        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_brand_id')->constrained('vehicle_brands')->onDelete('cascade');

            $table->string('name', 100);
            $table->string('slug', 100);

            // Classification
            $table->enum('vehicle_class', ['economy', 'business', 'luxury', 'van']);
            $table->enum('body_type', ['sedan', 'suv', 'van', 'coupe', 'convertible']);

            // Capacity
            $table->unsignedInteger('typical_passenger_capacity')->default(4);
            $table->unsignedInteger('typical_luggage_capacity')->default(2);

            // Media
            $table->string('image_url', 500)->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->unique(['vehicle_brand_id', 'slug']);
            $table->index('vehicle_brand_id');
            $table->index('vehicle_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
    }
};
