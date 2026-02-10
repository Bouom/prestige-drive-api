<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'customer', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'company', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'driver', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
