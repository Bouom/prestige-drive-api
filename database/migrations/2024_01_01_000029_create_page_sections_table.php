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
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade');

            // Section Type
            $table->enum('section_type', ['hero', 'text', 'image', 'gallery', 'features', 'cta', 'faq', 'custom']);

            // Content
            $table->json('content');

            // Styling
            $table->string('css_classes', 255)->nullable();
            $table->string('background_color', 7)->nullable();

            // Ordering
            $table->unsignedInteger('sort_order')->default(0);

            // Visibility
            $table->boolean('is_visible')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('page_id');
            $table->index('section_type');
            $table->index(['page_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
