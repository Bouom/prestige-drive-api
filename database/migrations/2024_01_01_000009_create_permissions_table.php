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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('display_name', 255);
            $table->text('description')->nullable();

            // Grouping
            $table->string('module', 100)->nullable();

            // Hierarchy
            $table->foreignId('parent_id')->nullable()->constrained('permissions')->onDelete('cascade');

            // Guard
            $table->string('guard_name', 100)->default('web');

            $table->timestamps();

            // Indexes
            $table->index('module');
            $table->index('guard_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
