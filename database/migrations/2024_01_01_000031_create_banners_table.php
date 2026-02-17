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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('subtitle', 255)->nullable();

            // Display
            $table->string('image_url', 500);
            $table->string('mobile_image_url', 500)->nullable();

            // Call to Action
            $table->string('cta_text', 100)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->boolean('opens_in_new_tab')->default(false);

            // Placement
            $table->enum('placement', ['homepage_hero', 'sidebar', 'footer', 'popup']);

            // Scheduling
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Ordering
            $table->unsignedInteger('sort_order')->default(0);

            // Status
            $table->boolean('is_active')->default(true);

            // Analytics
            $table->unsignedInteger('click_count')->default(0);
            $table->unsignedInteger('impression_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['placement', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
