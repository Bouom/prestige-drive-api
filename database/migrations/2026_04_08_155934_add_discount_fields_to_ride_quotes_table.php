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
        Schema::table('ride_quotes', function (Blueprint $table) {
            $table->string('discount_code', 50)->nullable()->after('estimated_price');
            $table->decimal('discount_amount', 10, 2)->default(0.00)->after('discount_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ride_quotes', function (Blueprint $table) {
            $table->dropColumn(['discount_code', 'discount_amount']);
        });
    }
};
