<?php

namespace Database\Seeders;

use App\Models\TripType;
use Illuminate\Database\Seeder;

class TripTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tripTypes = [
            [
                'name' => 'one_way',
                'display_name' => 'Aller Simple',
                'description' => 'Trajet aller simple du point A au point B',
                'is_active' => true,
            ],
            [
                'name' => 'round_trip',
                'display_name' => 'Aller-Retour',
                'description' => 'Trajet aller-retour avec retour au point de départ',
                'is_active' => true,
            ],
            [
                'name' => 'hourly',
                'display_name' => 'À la disposition',
                'description' => 'Mise à disposition du chauffeur pour une durée déterminée',
                'is_active' => true,
            ],
            [
                'name' => 'airport_transfer',
                'display_name' => 'Transfert Aéroport',
                'description' => 'Transfert depuis/vers un aéroport',
                'is_active' => true,
            ],
            [
                'name' => 'station_transfer',
                'display_name' => 'Transfert Gare',
                'description' => 'Transfert depuis/vers une gare',
                'is_active' => true,
            ],
            [
                'name' => 'long_distance',
                'display_name' => 'Longue Distance',
                'description' => 'Trajet de longue distance (plus de 100 km)',
                'is_active' => true,
            ],
            [
                'name' => 'event',
                'display_name' => 'Événementiel',
                'description' => 'Transport pour événements spéciaux (mariages, soirées, etc.)',
                'is_active' => true,
            ],
        ];

        foreach ($tripTypes as $type) {
            TripType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }

        $this->command->info('Trip types seeded successfully.');
    }
}
