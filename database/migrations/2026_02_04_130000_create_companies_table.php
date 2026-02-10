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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->unique();
            $table->string('company_name');
            $table->string('company_address')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('company_zip_code', 20)->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_country')->nullable();
            $table->unsignedInteger('driver_count')->nullable();
            $table->string('company_iban', 34)->nullable();
            $table->string('bic_code', 11)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
