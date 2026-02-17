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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();

            $table->text('question');
            $table->longText('answer');

            // Categorization
            $table->string('category', 100)->nullable();

            // Ordering
            $table->unsignedInteger('sort_order')->default(0);

            // Analytics
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('not_helpful_count')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index(['category', 'is_active']);
            $table->index('sort_order');
        });

        // Add fulltext index
        DB::statement('ALTER TABLE faqs ADD FULLTEXT INDEX idx_question (question, answer)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
