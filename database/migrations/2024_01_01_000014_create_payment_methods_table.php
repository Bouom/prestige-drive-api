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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Gateway
            $table->string('gateway', 50)->default('stripe');
            $table->string('gateway_payment_method_id', 255)->unique();

            // Card Details
            $table->enum('type', ['card', 'bank_account']);
            $table->string('card_brand', 50)->nullable();
            $table->char('card_last_four', 4)->nullable();
            $table->unsignedInteger('card_exp_month')->nullable();
            $table->unsignedInteger('card_exp_year')->nullable();

            // Bank Account
            $table->string('bank_name', 255)->nullable();
            $table->char('bank_account_last_four', 4)->nullable();

            // Status
            $table->boolean('is_default')->default(false);
            $table->boolean('is_verified')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
