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
        $promoCodes = 
            [
           
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
