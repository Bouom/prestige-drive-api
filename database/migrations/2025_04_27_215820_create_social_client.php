<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('oauth_clients')->insert([
            'name' => 'Social Grant Client',
            'secret' => Str::random(40),
            'redirect' => config('app.url'),
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'grants' => json_encode(['social', 'refresh_token']),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('oauth_clients')
            ->where('name', 'Social Grant Client')
            ->delete();
    }
};
