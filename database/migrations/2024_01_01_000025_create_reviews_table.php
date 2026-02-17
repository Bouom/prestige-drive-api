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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->unique()->constrained('rides')->onDelete('cascade');

            // Parties
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade');

            // Ratings
            $table->decimal('overall_rating', 3, 2);
            $table->unsignedInteger('cleanliness_rating')->nullable();
            $table->unsignedInteger('punctuality_rating')->nullable();
            $table->unsignedInteger('driving_quality_rating')->nullable();
            $table->unsignedInteger('professionalism_rating')->nullable();
            $table->unsignedInteger('vehicle_condition_rating')->nullable();

            // Text Review
            $table->text('comment')->nullable();

            // Response
            $table->text('driver_response')->nullable();
            $table->timestamp('driver_responded_at')->nullable();

            // Status
            $table->boolean('is_published')->default(true);
            $table->boolean('is_flagged')->default(false);
            $table->text('flagged_reason')->nullable();

            // Moderation
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('moderated_at')->nullable();
            $table->enum('moderation_action', ['approved', 'hidden', 'edited'])->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('ride_id');
            $table->index('reviewer_id');
            $table->index('reviewee_id');
            $table->index('overall_rating');
            $table->index(['is_published', 'is_flagged']);
        });

        // Add check constraints
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_overall_rating CHECK (overall_rating >= 1.00 AND overall_rating <= 5.00)');
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_cleanliness_rating CHECK (cleanliness_rating BETWEEN 1 AND 5)');
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_punctuality_rating CHECK (punctuality_rating BETWEEN 1 AND 5)');
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_driving_quality_rating CHECK (driving_quality_rating BETWEEN 1 AND 5)');
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_professionalism_rating CHECK (professionalism_rating BETWEEN 1 AND 5)');
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_vehicle_condition_rating CHECK (vehicle_condition_rating BETWEEN 1 AND 5)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
