<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User management
            ['name' => 'users.view', 'display_name' => 'View users', 'module' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create users', 'module' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit users', 'module' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete users', 'module' => 'users'],

            // Driver management
            ['name' => 'drivers.view', 'display_name' => 'View drivers', 'module' => 'drivers'],
            ['name' => 'drivers.verify', 'display_name' => 'Verify drivers', 'module' => 'drivers'],
            ['name' => 'drivers.edit', 'display_name' => 'Edit drivers', 'module' => 'drivers'],
            ['name' => 'drivers.delete', 'display_name' => 'Delete drivers', 'module' => 'drivers'],

            // Company management
            ['name' => 'companies.view', 'display_name' => 'View companies', 'module' => 'companies'],
            ['name' => 'companies.verify', 'display_name' => 'Verify companies', 'module' => 'companies'],
            ['name' => 'companies.edit', 'display_name' => 'Edit companies', 'module' => 'companies'],
            ['name' => 'companies.delete', 'display_name' => 'Delete companies', 'module' => 'companies'],

            // Ride management
            ['name' => 'rides.view', 'display_name' => 'View rides', 'module' => 'rides'],
            ['name' => 'rides.create', 'display_name' => 'Create rides', 'module' => 'rides'],
            ['name' => 'rides.edit', 'display_name' => 'Edit rides', 'module' => 'rides'],
            ['name' => 'rides.delete', 'display_name' => 'Delete rides', 'module' => 'rides'],
            ['name' => 'rides.assign', 'display_name' => 'Assign drivers to rides', 'module' => 'rides'],
            ['name' => 'rides.cancel', 'display_name' => 'Cancel rides', 'module' => 'rides'],

            // Payment management
            ['name' => 'payments.view', 'display_name' => 'View payments', 'module' => 'payments'],
            ['name' => 'payments.process', 'display_name' => 'Process payments', 'module' => 'payments'],
            ['name' => 'payments.refund', 'display_name' => 'Process refunds', 'module' => 'payments'],

            // Content management
            ['name' => 'cms.pages', 'display_name' => 'Manage pages', 'module' => 'cms'],
            ['name' => 'cms.news', 'display_name' => 'Manage news articles', 'module' => 'cms'],
            ['name' => 'cms.banners', 'display_name' => 'Manage banners', 'module' => 'cms'],
            ['name' => 'cms.partners', 'display_name' => 'Manage partners', 'module' => 'cms'],
            ['name' => 'cms.faqs', 'display_name' => 'Manage FAQs', 'module' => 'cms'],

            // Review management
            ['name' => 'reviews.view', 'display_name' => 'View reviews', 'module' => 'reviews'],
            ['name' => 'reviews.moderate', 'display_name' => 'Moderate reviews', 'module' => 'reviews'],
            ['name' => 'reviews.delete', 'display_name' => 'Delete reviews', 'module' => 'reviews'],

            // System settings
            ['name' => 'settings.view', 'display_name' => 'View settings', 'module' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'Edit settings', 'module' => 'settings'],

            // Reports and statistics
            ['name' => 'reports.view', 'display_name' => 'View reports', 'module' => 'reports'],
            ['name' => 'reports.export', 'display_name' => 'Export reports', 'module' => 'reports'],

            // Audit logs
            ['name' => 'audit.view', 'display_name' => 'View audit logs', 'module' => 'audit'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('Permissions seeded successfully.');
    }
}
