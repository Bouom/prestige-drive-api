<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userTypes = [
            [
                'name' => 'client',
                'display_name' => 'Client',
                'description' => 'Regular customer who books rides',
                'is_admin' => false,
                'is_driver' => false,
            ],
            [
                'name' => 'driver',
                'display_name' => 'Chauffeur',
                'description' => 'Driver who provides transportation services',
                'is_admin' => false,
                'is_driver' => true,
            ],
            [
                'name' => 'company',
                'display_name' => 'Société',
                'description' => 'Corporate account for business transportation',
                'is_admin' => false,
                'is_driver' => false,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'System administrator with full access',
                'is_admin' => true,
                'is_driver' => false,
            ],
        ];

        foreach ($userTypes as $type) {
            UserType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }

        $this->command->info('User types seeded successfully.');
    }
}
