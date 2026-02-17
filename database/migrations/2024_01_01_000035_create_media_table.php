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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();

            // Polymorphic Relationship
            $table->string('mediable_type', 100);
            $table->unsignedBigInteger('mediable_id');

            // File Details
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->unsignedInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();

            // Media Type
            $table->enum('media_type', ['image', 'video', 'audio']);

            // Classification
            $table->string('collection_name', 100)->nullable();

            // Image Metadata
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // Ordering
            $table->unsignedInteger('sort_order')->default(0);

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['mediable_type', 'mediable_id']);
            $table->index('collection_name');
            $table->index('media_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
