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
        Schema::create('ride_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained('rides')->onDelete('cascade');

            // Status Change
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);

            // Actor
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('changed_by_type', ['customer', 'driver', 'admin', 'system']);

            // Context
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Location at time of change
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('ride_id');
            $table->index('to_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_status_history');
    }
};
