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
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreign('current_driver_id')->references('id')->on('driver_profiles')->onDelete('set null');
        });

        Schema::table('ride_quotes', function (Blueprint $table) {
            $table->foreign('converted_to_ride_id')->references('id')->on('rides')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ride_quotes', function (Blueprint $table) {
            $table->dropForeign(['converted_to_ride_id']);
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['current_driver_id']);
        });
    }
};
