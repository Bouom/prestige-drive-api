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
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('slug', 255)->unique();
            $table->string('title', 255);
            $table->string('subtitle', 255)->nullable();

            // Content
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->string('featured_image_url', 500)->nullable();

            // Classification
            $table->string('category', 100)->nullable();
            $table->json('tags')->nullable();

            // SEO
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            // Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Authorship
            $table->foreignId('author_id')->constrained('users')->onDelete('restrict');

            // Analytics
            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index(['status', 'published_at']);
            $table->index('author_id');
            $table->index('category');
        });

        // Add fulltext index
        DB::statement('ALTER TABLE news_articles ADD FULLTEXT INDEX idx_content (title, content)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
