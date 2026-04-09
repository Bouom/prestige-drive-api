<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserTypeSeeder::class,
            TripTypeSeeder::class,
            DocumentTypeSeeder::class,
            LicenseTypeSeeder::class,
            PermissionSeeder::class,
            VehicleBrandAndModelSeeder::class,
            AdminUserSeeder::class,
            //DemoDataSeeder::class,
            //ContentSeeder::class,
            PrixSeeder::class,
        ]);

        $this->command->info('All reference data seeded successfully!');
    }
}
