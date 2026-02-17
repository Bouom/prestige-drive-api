<?php

namespace Database\Seeders;

use App\Models\Prix;
use App\Models\RetourChauffeur;
use Illuminate\Database\Seeder;

class PrixSeeder extends Seeder
{
    /**
     * Seed the prix and retour_chauffeur tables with legacy data.
     */
    public function run(): void
    {
        // Seed active prix entry (current production rate)
        Prix::updateOrCreate(
            ['montant' => 0.68],
            ['is_active' => true]
        );

        // Seed a selection of historical prix entries
        $prixEntries = [
            ['montant' => 0.45, 'is_active' => false],
            ['montant' => 0.50, 'is_active' => false],
            ['montant' => 0.55, 'is_active' => false],
            ['montant' => 0.57, 'is_active' => false],
            ['montant' => 0.60, 'is_active' => false],
            ['montant' => 0.62, 'is_active' => false],
            ['montant' => 0.70, 'is_active' => false],
            ['montant' => 0.72, 'is_active' => false],
            ['montant' => 0.75, 'is_active' => false],
            ['montant' => 0.78, 'is_active' => false],
            ['montant' => 0.84, 'is_active' => false],
            ['montant' => 0.85, 'is_active' => false],
        ];

        foreach ($prixEntries as $entry) {
            Prix::updateOrCreate(
                ['montant' => $entry['montant']],
                ['is_active' => $entry['is_active']]
            );
        }

        // Seed active retour chauffeur entry (current production fee)
        RetourChauffeur::updateOrCreate(
            ['montant' => 78.47],
            ['is_active' => true]
        );

        // Seed a selection of historical retour chauffeur entries
        $retourEntries = [
            ['montant' => 65.79, 'is_active' => false],
            ['montant' => 75.00, 'is_active' => false],
            ['montant' => 90.00, 'is_active' => false],
            ['montant' => 90.29, 'is_active' => false],
            ['montant' => 95.59, 'is_active' => false],
            ['montant' => 98.20, 'is_active' => false],
            ['montant' => 100.00, 'is_active' => false],
            ['montant' => 100.80, 'is_active' => false],
        ];

        foreach ($retourEntries as $entry) {
            RetourChauffeur::updateOrCreate(
                ['montant' => $entry['montant']],
                ['is_active' => $entry['is_active']]
            );
        }
    }
}
