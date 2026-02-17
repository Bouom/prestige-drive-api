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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('transaction_id', 100)->unique()->nullable();

            // Polymorphic Relationship
            $table->string('payable_type', 100);
            $table->unsignedBigInteger('payable_id');

            // Payer
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');

            // Payment Method
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('set null');
            $table->enum('payment_method_type', ['card', 'bank_transfer', 'cash', 'wallet']);

            // Amount
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');

            // Fee Breakdown
            $table->decimal('platform_fee', 10, 2)->default(0.00);
            $table->decimal('payment_processing_fee', 10, 2)->default(0.00);
            $table->decimal('net_amount', 10, 2)->nullable();

            // Status
            $table->enum('status', ['pending', 'processing', 'succeeded', 'failed', 'refunded', 'cancelled'])->default('pending');

            // Gateway Details
            $table->string('gateway', 50)->default('stripe');
            $table->json('gateway_response')->nullable();

            // Timestamps
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            // Error Handling
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();

            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['payable_type', 'payable_id']);
            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
