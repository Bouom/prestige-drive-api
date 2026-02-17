<?php

namespace Database\Seeders;

use App\Models\LicenseType;
use Illuminate\Database\Seeder;

class LicenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $licenseTypes = [
            [
                'code' => 'B',
                'name' => 'Permis B',
                'description' => 'Véhicules légers (voitures particulières)',
                'is_active' => true,
            ],
            [
                'code' => 'B+E',
                'name' => 'Permis B+E',
                'description' => 'Véhicules légers avec remorque',
                'is_active' => true,
            ],
            [
                'code' => 'C',
                'name' => 'Permis C',
                'description' => 'Véhicules de transport de marchandises > 3,5 tonnes',
                'is_active' => true,
            ],
            [
                'code' => 'D',
                'name' => 'Permis D',
                'description' => 'Véhicules de transport en commun (+ de 9 places)',
                'is_active' => true,
            ],
            [
                'code' => 'D1',
                'name' => 'Permis D1',
                'description' => 'Minibus (9 à 16 places)',
                'is_active' => true,
            ],
        ];

        foreach ($licenseTypes as $type) {
            LicenseType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }

        $this->command->info('License types seeded successfully.');
    }
}
