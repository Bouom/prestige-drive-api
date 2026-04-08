<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promoCodes = [
            [
                'code' => 'WELCOME20',
                'description' => 'Réduction de bienvenue 20% pour tous les nouveaux clients',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'target_type' => 'all',
                'starts_at' => now(),
                'max_uses' => 100,
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'COMPANY15',
                'description' => 'Réduction 15€ pour les sociétés',
                'discount_type' => 'fixed',
                'discount_value' => 15.00,
                'target_type' => 'company',
                'starts_at' => now(),
                'max_uses' => 50,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'INDIVIDUAL10',
                'description' => 'Réduction 10% pour les particuliers',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'target_type' => 'individual',
                'starts_at' => now(),
                'max_uses' => 200,
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'code' => 'FLASH50',
                'description' => 'Offre flash - 50€ de réduction',
                'discount_type' => 'fixed',
                'discount_value' => 50.00,
                'target_type' => 'all',
                'starts_at' => now()->addDays(1),
                'max_uses' => 10,
                'expires_at' => now()->addDays(7),
                'is_active' => true,
            ],
        ];

        foreach ($promoCodes as $promoCodeData) {
            PromoCode::updateOrCreate(
                ['code' => $promoCodeData['code']],
                $promoCodeData
            );
        }

        $this->command->info('Promo codes seeded successfully.');
    }
}
