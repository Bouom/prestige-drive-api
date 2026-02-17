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
        Schema::create('notifications', function (Blueprint $table) {
            $table->char('id', 36)->primary();

            // Recipient (Polymorphic)
            $table->string('notifiable_type', 100);
            $table->unsignedBigInteger('notifiable_id');

            // Notification Type
            $table->string('type', 255);

            // Content
            $table->json('data');

            // Status
            $table->timestamp('read_at')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('read_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
