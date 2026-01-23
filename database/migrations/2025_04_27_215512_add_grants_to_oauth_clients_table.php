<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->json('grants')->nullable()->after('password_client');
        });

        // Update existing password client if needed
        DB::table('oauth_clients')
            ->where('password_client', true)
            ->update(['grants' => json_encode(['password', 'refresh_token'])]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropColumn('grants');
        });
    }
};
