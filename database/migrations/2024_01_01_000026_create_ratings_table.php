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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();

            // Polymorphic Relationship
            $table->string('rateable_type', 100);
            $table->unsignedBigInteger('rateable_id');

            // Aggregates
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('total_ratings')->default(0);

            // Distribution
            $table->json('rating_distribution')->nullable();

            // Dimension Averages
            $table->decimal('average_cleanliness', 3, 2)->nullable();
            $table->decimal('average_punctuality', 3, 2)->nullable();
            $table->decimal('average_driving_quality', 3, 2)->nullable();
            $table->decimal('average_professionalism', 3, 2)->nullable();
            $table->decimal('average_vehicle_condition', 3, 2)->nullable();

            // Recalculation
            $table->timestamp('last_calculated_at')->useCurrent();

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->unique(['rateable_type', 'rateable_id']);
            $table->index('average_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
