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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->onDelete('set null');

            // Identification
            $table->string('slug', 255)->unique();
            $table->string('title', 255);
            $table->string('subtitle', 255)->nullable();

            // Content
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();

            // SEO
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Layout
            $table->string('template', 100)->default('default');

            // Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Authorship
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');

            // Ordering
            $table->unsignedInteger('sort_order')->default(0);

            // Analytics
            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('parent_id');
            $table->index(['status', 'published_at']);
            $table->index('author_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
