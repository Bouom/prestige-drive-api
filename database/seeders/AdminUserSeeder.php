<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user and assign all permissions to the admin role.
     */
    public function run(): void
    {
        // ── 1. Attach all permissions to the admin user type ──────────────────
        $adminType = UserType::where('name', 'admin')->firstOrFail();
        $allPermissions = Permission::all();
        $adminType->permissions()->sync($allPermissions->pluck('id')->toArray());

        $this->command->info("Attached {$allPermissions->count()} permissions to admin role.");

        // ── 2. Create (or update) the default admin account ──────────────────
        $email = env('ADMIN_EMAIL', 'admin@louerunchauffeurprestige.fr');
        $password = env('ADMIN_PASSWORD', 'Admin@LCP2024!');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'user_type_id' => $adminType->id,
                'first_name' => env('ADMIN_FIRST_NAME', 'Alexandre'),
                'last_name' => env('ADMIN_LAST_NAME', 'Dupont'),
                'phone' => env('ADMIN_PHONE', '+33612345678'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_active' => true,
                'is_verified' => true,
                'language' => 'fr',
                'timezone' => 'Europe/Paris',
                'gender' => 'male',
                'city' => 'Paris',
                'country' => 'FR',
            ]
        );

        $this->command->info("Admin account ready: {$admin->email}");
        $this->command->line("  → Name     : {$admin->full_name}");
        $this->command->line("  → Password : {$password}");
        $this->command->line("  → UUID     : {$admin->uuid}");
    }
}
