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
            $table->enum('customer_type', ['company', 'individual'])->nullable()->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ride_quotes', function (Blueprint $table) {
            $table->dropColumn('customer_type');
        });
    }
};
