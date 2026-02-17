<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\DriverProfile;
use App\Models\LicenseType;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Ride;
use App\Models\TripType;
use App\Models\User;
use App\Models\UserType;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $clientType = UserType::where('name', 'client')->firstOrFail();
        $driverType = UserType::where('name', 'driver')->firstOrFail();
        $companyType = UserType::where('name', 'company')->firstOrFail();

        $licenseB = LicenseType::where('code', 'B')->firstOrFail();

        // Preferred vehicle models (Mercedes E/S, BMW 5/7, Audi A6/A8)
        $models = [
            'merc_e' => VehicleModel::where('vehicle_brand_id', 4)->where('name', 'Classe E')->first(),
            'merc_s' => VehicleModel::where('vehicle_brand_id', 4)->where('name', 'Classe S')->first(),
            'merc_v' => VehicleModel::where('vehicle_brand_id', 4)->where('name', 'Classe V')->first(),
            'bmw_5' => VehicleModel::where('vehicle_brand_id', 1)->where('name', 'Série 5')->first(),
            'bmw_7' => VehicleModel::where('vehicle_brand_id', 1)->where('name', 'Série 7')->first(),
            'audi_a6' => VehicleModel::where('vehicle_brand_id', 11)->where('name', 'A6')->first(),
            'audi_a8' => VehicleModel::where('vehicle_brand_id', 11)->where('name', 'A8')->first(),
        ];

        $tripTypes = TripType::all()->keyBy('name');

        // ─────────────────────────────────────────
        // 1.  CLIENT USERS  (20)
        // ─────────────────────────────────────────
        $clientsData = [
            ['first_name' => 'Sophie',    'last_name' => 'Martin',    'email' => 'sophie.martin@example.fr',    'city' => 'Paris'],
            ['first_name' => 'Luc',       'last_name' => 'Dubois',    'email' => 'luc.dubois@example.fr',       'city' => 'Paris'],
            ['first_name' => 'Camille',   'last_name' => 'Bernard',   'email' => 'camille.bernard@example.fr',  'city' => 'Lyon'],
            ['first_name' => 'Pierre',    'last_name' => 'Thomas',    'email' => 'pierre.thomas@example.fr',    'city' => 'Marseille'],
            ['first_name' => 'Julie',     'last_name' => 'Petit',     'email' => 'julie.petit@example.fr',      'city' => 'Paris'],
            ['first_name' => 'Antoine',   'last_name' => 'Robert',    'email' => 'antoine.robert@example.fr',   'city' => 'Bordeaux'],
            ['first_name' => 'Marie',     'last_name' => 'Richard',   'email' => 'marie.richard@example.fr',    'city' => 'Paris'],
            ['first_name' => 'Nicolas',   'last_name' => 'Durand',    'email' => 'nicolas.durand@example.fr',   'city' => 'Toulouse'],
            ['first_name' => 'Isabelle',  'last_name' => 'Laurent',   'email' => 'isabelle.laurent@example.fr', 'city' => 'Nice'],
            ['first_name' => 'François',  'last_name' => 'Simon',     'email' => 'francois.simon@example.fr',   'city' => 'Paris'],
            ['first_name' => 'Nathalie',  'last_name' => 'Michel',    'email' => 'nathalie.michel@example.fr',  'city' => 'Nantes'],
            ['first_name' => 'Éric',      'last_name' => 'Lefebvre',  'email' => 'eric.lefebvre@example.fr',    'city' => 'Paris'],
            ['first_name' => 'Chloé',     'last_name' => 'Leroy',     'email' => 'chloe.leroy@example.fr',      'city' => 'Strasbourg'],
            ['first_name' => 'David',     'last_name' => 'Roux',      'email' => 'david.roux@example.fr',       'city' => 'Paris'],
            ['first_name' => 'Amélie',    'last_name' => 'Moreau',    'email' => 'amelie.moreau@example.fr',    'city' => 'Lille'],
            ['first_name' => 'Julien',    'last_name' => 'Fournier',  'email' => 'julien.fournier@example.fr',  'city' => 'Paris'],
            ['first_name' => 'Laure',     'last_name' => 'Girard',    'email' => 'laure.girard@example.fr',     'city' => 'Rennes'],
            ['first_name' => 'Marc',      'last_name' => 'Bonnet',    'email' => 'marc.bonnet@example.fr',      'city' => 'Paris'],
            ['first_name' => 'Stéphanie', 'last_name' => 'Dupont',    'email' => 'stephanie.dupont@example.fr', 'city' => 'Montpellier'],
            ['first_name' => 'Thomas',   'last_name' => 'Lambert',   'email' => 'thomas.lambert@example.fr',   'city' => 'Paris'],
        ];

        $clients = [];
        foreach ($clientsData as $i => $cd) {
            $clients[] = User::updateOrCreate(
                ['email' => $cd['email']],
                [
                    'user_type_id' => $clientType->id,
                    'first_name' => $cd['first_name'],
                    'last_name' => $cd['last_name'],
                    'phone' => '+336'.str_pad($i * 7 + 10000001, 8, '0', STR_PAD_LEFT),
                    'password' => Hash::make('Demo2024'),
                    'email_verified_at' => now()->subDays(rand(10, 365)),
                    'is_active' => true,
                    'is_verified' => true,
                    'language' => 'fr',
                    'timezone' => 'Europe/Paris',
                    'city' => $cd['city'],
                    'country' => 'FR',
                ]
            );
        }

        // ─────────────────────────────────────────
        // 2.  COMPANIES  (4)
        // ─────────────────────────────────────────
        $companiesData = [
            [
                'user' => ['first_name' => 'Laurent', 'last_name' => 'Berger', 'email' => 'laurent.berger@prestige-paris.fr'],
                'company' => [
                    'legal_name' => 'Prestige Paris Transport SAS',
                    'trade_name' => 'Prestige Paris',
                    'registration_number' => 'RCS-2019-123456',
                    'email' => 'contact@prestige-paris.fr',
                    'phone' => '+33142000001',
                    'address' => '15 Avenue des Champs-Élysées',
                    'postal_code' => '75008',
                    'city' => 'Paris',
                    'representative_name' => 'Laurent Berger',
                    'representative_position' => 'Directeur Général',
                    'is_verified' => true,
                    'total_drivers' => 6,
                    'active_drivers' => 5,
                    'total_vehicles' => 6,
                ],
            ],
            [
                'user' => ['first_name' => 'Isabelle', 'last_name' => 'Faure', 'email' => 'isabelle.faure@luxury-drive.fr'],
                'company' => [
                    'legal_name' => 'Luxury Drive France SARL',
                    'trade_name' => 'Luxury Drive',
                    'registration_number' => 'RCS-2020-654321',
                    'email' => 'contact@luxury-drive.fr',
                    'phone' => '+33156000002',
                    'address' => '8 Rue de la Paix',
                    'postal_code' => '75002',
                    'city' => 'Paris',
                    'representative_name' => 'Isabelle Faure',
                    'representative_position' => 'Gérante',
                    'is_verified' => true,
                    'total_drivers' => 4,
                    'active_drivers' => 4,
                    'total_vehicles' => 4,
                ],
            ],
            [
                'user' => ['first_name' => 'Alain', 'last_name' => 'Courtois', 'email' => 'alain.courtois@vtc-lyon.fr'],
                'company' => [
                    'legal_name' => 'VTC Lyon Premium SA',
                    'trade_name' => 'Lyon Premium',
                    'registration_number' => 'RCS-2021-789012',
                    'email' => 'contact@vtc-lyon.fr',
                    'phone' => '+33478000003',
                    'address' => '22 Rue de la République',
                    'postal_code' => '69001',
                    'city' => 'Lyon',
                    'representative_name' => 'Alain Courtois',
                    'representative_position' => 'PDG',
                    'is_verified' => false,  // pending
                    'total_drivers' => 3,
                    'active_drivers' => 2,
                    'total_vehicles' => 3,
                ],
            ],
            [
                'user' => ['first_name' => 'Bertrand', 'last_name' => 'Morin', 'email' => 'bertrand.morin@executive-transfer.fr'],
                'company' => [
                    'legal_name' => 'Executive Transfer Méditerranée SAS',
                    'trade_name' => 'Executive Transfer',
                    'registration_number' => 'RCS-2022-345678',
                    'email' => 'contact@executive-transfer.fr',
                    'phone' => '+33491000004',
                    'address' => '5 Boulevard de la Canebière',
                    'postal_code' => '13001',
                    'city' => 'Marseille',
                    'representative_name' => 'Bertrand Morin',
                    'representative_position' => 'Directeur',
                    'is_verified' => true,
                    'total_drivers' => 2,
                    'active_drivers' => 2,
                    'total_vehicles' => 2,
                ],
            ],
        ];

        $companies = [];
        foreach ($companiesData as $cd) {
            $companyUser = User::updateOrCreate(
                ['email' => $cd['user']['email']],
                [
                    'user_type_id' => $companyType->id,
                    'first_name' => $cd['user']['first_name'],
                    'last_name' => $cd['user']['last_name'],
                    'phone' => $cd['company']['phone'],
                    'password' => Hash::make('Demo2024'),
                    'email_verified_at' => now()->subDays(rand(30, 500)),
                    'is_active' => true,
                    'is_verified' => $cd['company']['is_verified'],
                    'language' => 'fr',
                    'timezone' => 'Europe/Paris',
                    'city' => $cd['company']['city'],
                    'country' => 'FR',
                ]
            );

            $company = Company::updateOrCreate(
                ['registration_number' => $cd['company']['registration_number']],
                array_merge($cd['company'], [
                    'uuid' => Str::uuid(),
                    'verified_at' => $cd['company']['is_verified'] ? now()->subDays(rand(10, 300)) : null,
                ])
            );

            $companyUser->companies()->syncWithoutDetaching([$company->id]);
            $companies[] = $company;
        }

        // ─────────────────────────────────────────
        // 3.  INDEPENDENT DRIVERS  (6)
        // ─────────────────────────────────────────
        $driversData = [
            ['first_name' => 'Karim',   'last_name' => 'Benali',    'email' => 'karim.benali@chauffeur.fr',    'company_idx' => null, 'model_key' => 'merc_e',  'plate' => 'AB-123-CD', 'rides' => 42, 'earn' => 3200.00, 'accept' => 97.2, 'cancel' => 1.5, 'verified' => true,  'avail' => true],
            ['first_name' => 'Moussa',  'last_name' => 'Diallo',    'email' => 'moussa.diallo@chauffeur.fr',   'company_idx' => null, 'model_key' => 'bmw_5',   'plate' => 'EF-456-GH', 'rides' => 31, 'earn' => 2450.00, 'accept' => 94.5, 'cancel' => 2.1, 'verified' => true,  'avail' => true],
            ['first_name' => 'Jean-Luc', 'last_name' => 'Perrin',    'email' => 'jeanluc.perrin@chauffeur.fr',  'company_idx' => null, 'model_key' => 'audi_a6', 'plate' => 'IJ-789-KL', 'rides' => 18, 'earn' => 1350.00, 'accept' => 88.0, 'cancel' => 4.2, 'verified' => true,  'avail' => false],
            ['first_name' => 'Ahmed',   'last_name' => 'Cherif',    'email' => 'ahmed.cherif@chauffeur.fr',    'company_idx' => null, 'model_key' => 'merc_s',  'plate' => 'MN-321-OP', 'rides' => 57, 'earn' => 5100.00, 'accept' => 98.8, 'cancel' => 0.8, 'verified' => true,  'avail' => true],
            ['first_name' => 'Patrick', 'last_name' => 'Nguyen',    'email' => 'patrick.nguyen@chauffeur.fr',  'company_idx' => null, 'model_key' => 'bmw_7',   'plate' => 'QR-654-ST', 'rides' => 9,  'earn' => 720.00,  'accept' => 80.0, 'cancel' => 8.0, 'verified' => false, 'avail' => false],
            ['first_name' => 'Samir',   'last_name' => 'Hadj',      'email' => 'samir.hadj@chauffeur.fr',      'company_idx' => null, 'model_key' => 'audi_a8', 'plate' => 'UV-987-WX', 'rides' => 73, 'earn' => 6800.00, 'accept' => 99.1, 'cancel' => 0.5, 'verified' => true,  'avail' => true],
        ];

        // Company drivers (attached to companies 0 and 1)
        $companyDriversData = [
            // Prestige Paris (company 0) – 3 drivers
            ['first_name' => 'Thierry',  'last_name' => 'Blanc',   'email' => 'thierry.blanc@prestige-paris.fr',  'company_idx' => 0, 'model_key' => 'merc_e',  'plate' => 'PA-001-PP', 'rides' => 65, 'earn' => 5850.00, 'accept' => 98.0, 'cancel' => 1.2, 'verified' => true, 'avail' => true],
            ['first_name' => 'Rachid',   'last_name' => 'Amara',   'email' => 'rachid.amara@prestige-paris.fr',   'company_idx' => 0, 'model_key' => 'merc_s',  'plate' => 'PA-002-PP', 'rides' => 49, 'earn' => 4600.00, 'accept' => 96.3, 'cancel' => 2.0, 'verified' => true, 'avail' => true],
            ['first_name' => 'Fabrice',  'last_name' => 'Dupuis',  'email' => 'fabrice.dupuis@prestige-paris.fr', 'company_idx' => 0, 'model_key' => 'merc_v',  'plate' => 'PA-003-PP', 'rides' => 38, 'earn' => 3400.00, 'accept' => 93.5, 'cancel' => 3.1, 'verified' => true, 'avail' => false],
            // Luxury Drive (company 1) – 2 drivers
            ['first_name' => 'Sébastien', 'last_name' => 'Mercier', 'email' => 'sebastien.mercier@luxury-drive.fr', 'company_idx' => 1, 'model_key' => 'bmw_7',   'plate' => 'LD-001-LX', 'rides' => 28, 'earn' => 2900.00, 'accept' => 91.0, 'cancel' => 3.5, 'verified' => true, 'avail' => true],
            ['first_name' => 'Véronique', 'last_name' => 'Collin',  'email' => 'veronique.collin@luxury-drive.fr', 'company_idx' => 1, 'model_key' => 'audi_a6', 'plate' => 'LD-002-LX', 'rides' => 21, 'earn' => 2100.00, 'accept' => 89.0, 'cancel' => 4.0, 'verified' => true, 'avail' => true],
            // Executive Transfer (company 3) – 1 driver
            ['first_name' => 'Hassan',   'last_name' => 'Bouali',  'email' => 'hassan.bouali@executive-transfer.fr', 'company_idx' => 3, 'model_key' => 'merc_e', 'plate' => 'ET-001-MA', 'rides' => 14, 'earn' => 1200.00, 'accept' => 85.0, 'cancel' => 6.0, 'verified' => true, 'avail' => true],
        ];

        $allDriversData = array_merge($driversData, $companyDriversData);

        $driverProfiles = [];
        foreach ($allDriversData as $i => $dd) {
            $driverUser = User::updateOrCreate(
                ['email' => $dd['email']],
                [
                    'user_type_id' => $driverType->id,
                    'first_name' => $dd['first_name'],
                    'last_name' => $dd['last_name'],
                    'phone' => '+336'.str_pad(($i + 1) * 3 + 20000000, 8, '0', STR_PAD_LEFT),
                    'password' => Hash::make('Demo2024'),
                    'email_verified_at' => now()->subDays(rand(30, 700)),
                    'is_active' => true,
                    'is_verified' => $dd['verified'],
                    'language' => 'fr',
                    'timezone' => 'Europe/Paris',
                    'city' => 'Paris',
                    'country' => 'FR',
                ]
            );

            $modelRecord = $models[$dd['model_key']];
            $companyRecord = ($dd['company_idx'] !== null) ? $companies[$dd['company_idx']] : null;

            // Create vehicle
            $vehicle = Vehicle::updateOrCreate(
                ['license_plate' => $dd['plate']],
                [
                    'uuid' => Str::uuid(),
                    'company_id' => $companyRecord?->id,
                    'current_driver_id' => null, // set after profile
                    'vehicle_model_id' => $modelRecord ? $modelRecord->id : VehicleModel::first()->id,
                    'year' => rand(2019, 2023),
                    'color' => collect(['Noir', 'Gris anthracite', 'Blanc nacré', 'Argent'])->random(),
                    'fuel_type' => collect(['diesel', 'hybrid', 'electric'])->random(),
                    'transmission' => 'automatic',
                    'passenger_capacity' => ($dd['model_key'] === 'merc_v') ? 7 : 4,
                    'luggage_capacity' => ($dd['model_key'] === 'merc_v') ? 4 : 2,
                    'vehicle_class' => ($dd['model_key'] === 'merc_v') ? 'van' : 'business',
                    'is_active' => true,
                    'is_available' => $dd['avail'],
                    'current_status' => $dd['avail'] ? 'available' : 'maintenance',
                    'insurance_company' => 'AXA',
                    'insurance_expires_at' => now()->addYear()->toDateString(),
                    'total_km' => rand(15000, 85000),
                ]
            );

            $profile = DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'company_id' => $companyRecord?->id,
                    'vehicle_id' => $vehicle->id,
                    'license_type_id' => $licenseB->id,
                    'license_number' => 'VTC-'.strtoupper(Str::random(8)),
                    'license_issued_at' => now()->subYears(rand(3, 15))->toDateString(),
                    'license_expires_at' => now()->addYears(rand(1, 5))->toDateString(),
                    'employment_type' => $companyRecord ? 'company_employed' : 'independent',
                    'is_available' => $dd['avail'],
                    'is_verified' => $dd['verified'],
                    'verified_at' => $dd['verified'] ? now()->subDays(rand(5, 200)) : null,
                    'total_rides' => $dd['rides'],
                    'completed_rides' => (int) ($dd['rides'] * 0.92),
                    'cancelled_rides' => (int) ($dd['rides'] * 0.05),
                    'acceptance_rate' => $dd['accept'],
                    'cancellation_rate' => $dd['cancel'],
                    'total_earnings' => $dd['earn'],
                    'years_experience' => rand(2, 12),
                    'max_passengers' => ($dd['model_key'] === 'merc_v') ? 7 : 4,
                    'bio' => 'Chauffeur professionnel expérimenté, à votre service pour tous vos déplacements.',
                ]
            );

            // Link vehicle back to driver
            $vehicle->update(['current_driver_id' => $profile->id]);

            $driverProfiles[] = $profile;
        }

        // ─────────────────────────────────────────
        // 4.  RIDES (60 rides with varied statuses)
        // ─────────────────────────────────────────
        $parisRoutes = [
            ['pickup' => '1 Place du Trocadéro, Paris 16',  'plat' => 48.8614, 'plng' => 2.2886,  'dropoff' => 'Aéroport CDG, Roissy',              'dlat' => 49.0097, 'dlng' => 2.5479,  'dist' => 32.5, 'dur' => 45],
            ['pickup' => '15 Rue de Rivoli, Paris 1',        'plat' => 48.8566, 'plng' => 2.3522,  'dropoff' => 'Aéroport d\'Orly, Terminal 3',      'dlat' => 48.7262, 'dlng' => 2.3652,  'dist' => 18.2, 'dur' => 30],
            ['pickup' => 'Gare de Lyon, Paris 12',           'plat' => 48.8448, 'plng' => 2.3735,  'dropoff' => 'Gare du Nord, Paris 10',            'dlat' => 48.8809, 'dlng' => 2.3553,  'dist' => 5.8,  'dur' => 18],
            ['pickup' => '8 Boulevard Haussmann, Paris 9',   'plat' => 48.8737, 'plng' => 2.3356,  'dropoff' => 'La Défense, Puteaux',               'dlat' => 48.8924, 'dlng' => 2.2386,  'dist' => 12.1, 'dur' => 25],
            ['pickup' => 'Hôtel de Crillon, Paris 8',        'plat' => 48.8672, 'plng' => 2.3217,  'dropoff' => 'Versailles, Château',               'dlat' => 48.8049, 'dlng' => 2.1204,  'dist' => 23.4, 'dur' => 40],
            ['pickup' => '22 Rue du Faubourg Saint-Antoine', 'plat' => 48.8532, 'plng' => 2.3712,  'dropoff' => 'Stade de France, Saint-Denis',      'dlat' => 48.9244, 'dlng' => 2.3601,  'dist' => 10.2, 'dur' => 22],
            ['pickup' => 'Opéra Garnier, Paris 9',           'plat' => 48.8720, 'plng' => 2.3316,  'dropoff' => 'Disneyland Paris, Marne-la-Vallée', 'dlat' => 48.8676, 'dlng' => 2.7796,  'dist' => 42.0, 'dur' => 55],
            ['pickup' => 'Tour Eiffel, Paris 7',             'plat' => 48.8584, 'plng' => 2.2945,  'dropoff' => 'Musée du Louvre, Paris 1',          'dlat' => 48.8606, 'dlng' => 2.3376,  'dist' => 4.5,  'dur' => 14],
            ['pickup' => 'Le Marais, Paris 4',               'plat' => 48.8546, 'plng' => 2.3534,  'dropoff' => 'Montmartre, Paris 18',              'dlat' => 48.8867, 'dlng' => 2.3431,  'dist' => 4.8,  'dur' => 15],
            ['pickup' => 'Champs-Élysées, Paris 8',          'plat' => 48.8698, 'plng' => 2.3078,  'dropoff' => 'Aéroport Le Bourget',               'dlat' => 48.9693, 'dlng' => 2.4416,  'dist' => 27.3, 'dur' => 38],
        ];

        $rideStatuses = [
            ['status' => 'completed',           'payment' => 'paid',    'count' => 25],
            ['status' => 'in_progress',         'payment' => 'authorized', 'count' => 4],
            ['status' => 'accepted',            'payment' => 'pending', 'count' => 4],
            ['status' => 'confirmed',           'payment' => 'pending', 'count' => 5],
            ['status' => 'cancelled_by_customer', 'payment' => 'refunded', 'count' => 7],
            ['status' => 'cancelled_by_driver', 'payment' => 'refunded', 'count' => 4],
            ['status' => 'pending',             'payment' => 'pending', 'count' => 6],
            ['status' => 'no_show',             'payment' => 'failed',  'count' => 2],
            ['status' => 'assigned',            'payment' => 'pending', 'count' => 3],
        ];

        $ridesCreated = [];
        $rideIndex = 0;
        $verifiedDrivers = array_filter($driverProfiles, fn ($p) => $p->is_verified);
        $verifiedDriversArr = array_values($verifiedDrivers);

        foreach ($rideStatuses as $statusGroup) {
            for ($k = 0; $k < $statusGroup['count']; $k++) {
                $route = $parisRoutes[$rideIndex % count($parisRoutes)];
                $client = $clients[$rideIndex % count($clients)];
                $driver = count($verifiedDriversArr) ? $verifiedDriversArr[$rideIndex % count($verifiedDriversArr)] : null;
                $isCompleted = $statusGroup['status'] === 'completed';
                $isCancelled = str_starts_with($statusGroup['status'], 'cancelled') || $statusGroup['status'] === 'no_show';
                $isInProgress = $statusGroup['status'] === 'in_progress';
                $isAccepted = in_array($statusGroup['status'], ['accepted', 'assigned', 'in_progress', 'completed', 'no_show']);

                $basePrice = round($route['dist'] * 1.8 + 8.0, 2);
                $taxes = round($basePrice * 0.20, 2);
                $platformFee = round($basePrice * 0.15, 2);
                $finalPrice = round($basePrice + $taxes, 2);

                $tripType = collect($tripTypes)->random();
                $daysAgo = $isCompleted ? rand(1, 90) : ($isCancelled ? rand(1, 60) : rand(0, 14));
                $scheduledAt = now()->subDays($daysAgo)->setHour(rand(6, 22))->setMinute(rand(0, 59));

                $ride = Ride::create([
                    'uuid' => Str::uuid(),
                    'booking_reference' => 'LCP-'.date('Y').'-'.str_pad($rideIndex + 1000, 6, '0', STR_PAD_LEFT),
                    'customer_id' => $client->id,
                    'driver_id' => $isAccepted && $driver ? $driver->id : null,
                    'company_id' => ($driver && $driver->company_id) ? $driver->company_id : null,
                    'trip_type_id' => $tripType->id,
                    'vehicle_id' => ($isAccepted && $driver) ? $driver->vehicle_id : null,
                    'pickup_address' => $route['pickup'],
                    'pickup_latitude' => $route['plat'] + (rand(-100, 100) / 10000),
                    'pickup_longitude' => $route['plng'] + (rand(-100, 100) / 10000),
                    'dropoff_address' => $route['dropoff'],
                    'dropoff_latitude' => $route['dlat'],
                    'dropoff_longitude' => $route['dlng'],
                    'scheduled_at' => $scheduledAt,
                    'accepted_at' => $isAccepted ? $scheduledAt->copy()->subMinutes(rand(5, 30)) : null,
                    'started_at' => ($isInProgress || $isCompleted) ? $scheduledAt->copy()->addMinutes(rand(0, 5)) : null,
                    'completed_at' => $isCompleted ? $scheduledAt->copy()->addMinutes($route['dur'] + rand(-3, 5)) : null,
                    'cancelled_at' => $isCancelled ? $scheduledAt->copy()->subHours(rand(1, 12)) : null,
                    'estimated_distance_km' => $route['dist'],
                    'estimated_duration_min' => $route['dur'],
                    'actual_distance_km' => $isCompleted ? $route['dist'] + (rand(-5, 10) / 10) : null,
                    'actual_duration_min' => $isCompleted ? $route['dur'] + rand(-2, 8) : null,
                    'passenger_count' => rand(1, 3),
                    'has_luggage' => (bool) rand(0, 1),
                    'requires_child_seat' => false,
                    'is_round_trip' => false,
                    'base_price' => $basePrice,
                    'return_fee' => 0,
                    'surcharge' => 0,
                    'discount_amount' => 0,
                    'total_price' => $basePrice,
                    'platform_fee' => $platformFee,
                    'taxes' => $taxes,
                    'final_price' => $finalPrice,
                    'driver_earnings' => $isCompleted ? round($basePrice * 0.85, 2) : null,
                    'status' => $statusGroup['status'],
                    'payment_status' => $statusGroup['payment'],
                    'customer_notes' => rand(0, 1) ? 'Merci de sonner à l\'arrivée.' : null,
                    'created_at' => $scheduledAt->copy()->subHours(rand(1, 48)),
                    'updated_at' => now()->subDays(rand(0, $daysAgo)),
                ]);

                $ridesCreated[] = $ride;
                $rideIndex++;

                // Create payment for paid/completed rides
                if (in_array($statusGroup['payment'], ['paid', 'authorized'])) {
                    $payStatus = $statusGroup['payment'] === 'paid' ? 'succeeded' : 'processing';
                    Payment::create([
                        'uuid' => Str::uuid(),
                        'transaction_id' => 'pi_'.Str::random(24),
                        'payable_type' => Ride::class,
                        'payable_id' => $ride->id,
                        'user_id' => $client->id,
                        'payment_method_type' => collect(['card', 'card', 'card', 'bank_transfer'])->random(),
                        'amount' => $finalPrice,
                        'currency' => 'EUR',
                        'platform_fee' => $platformFee,
                        'payment_processing_fee' => round($finalPrice * 0.014 + 0.25, 2),
                        'net_amount' => round($finalPrice - $platformFee - 0.25, 2),
                        'status' => $payStatus,
                        'gateway' => 'stripe',
                        'authorized_at' => $ride->accepted_at ?? $scheduledAt,
                        'captured_at' => $isCompleted ? $ride->completed_at : null,
                        'ip_address' => '82.67.'.rand(1, 254).'.'.rand(1, 254),
                        'created_at' => $ride->created_at,
                        'updated_at' => now(),
                    ]);
                }

                // Create review for completed rides (70% chance)
                if ($isCompleted && $driver && rand(1, 10) <= 7) {
                    $rating = rand(35, 50) / 10; // 3.5 – 5.0
                    Review::create([
                        'ride_id' => $ride->id,
                        'reviewer_id' => $client->id,
                        'reviewee_id' => $driver->user_id,
                        'overall_rating' => $rating,
                        'cleanliness_rating' => rand(3, 5),
                        'punctuality_rating' => rand(3, 5),
                        'driving_quality_rating' => rand(3, 5),
                        'professionalism_rating' => rand(4, 5),
                        'vehicle_condition_rating' => rand(3, 5),
                        'comment' => collect([
                            'Excellent service, ponctuel et très professionnel.',
                            'Très bon chauffeur, voiture impeccable.',
                            'Service de qualité, je recommande vivement.',
                            'Chauffeur agréable et conduite douce.',
                            'Parfait pour un déplacement professionnel.',
                            'Très satisfait, bonne connaissance des itinéraires.',
                            null,
                        ])->random(),
                        'is_published' => true,
                        'created_at' => $ride->completed_at->copy()->addHours(rand(1, 24)),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // ─────────────────────────────────────────
        // 5.  RIDE QUOTES / SIMULATIONS  (5)
        // ─────────────────────────────────────────
        $quoteRoutes = array_slice($parisRoutes, 0, 5);
        foreach ($quoteRoutes as $j => $route) {
            $client = $clients[$j % count($clients)];
            $model = array_values($models)[($j + 2) % count($models)];
            DB::table('ride_quotes')->insert([
                'user_id' => $client->id,
                'trip_type_id' => $tripTypes->first()->id,
                'vehicle_model_id' => $model ? $model->id : VehicleModel::first()->id,
                'vehicle_brand_id' => $model ? $model->vehicle_brand_id : VehicleModel::first()->vehicle_brand_id,
                'pickup_address' => $route['pickup'],
                'pickup_latitude' => $route['plat'],
                'pickup_longitude' => $route['plng'],
                'dropoff_address' => $route['dropoff'],
                'dropoff_latitude' => $route['dlat'],
                'dropoff_longitude' => $route['dlng'],
                'scheduled_at' => now()->addDays(rand(1, 30)),
                'estimated_distance_km' => $route['dist'],
                'estimated_duration_min' => $route['dur'],
                'passenger_count' => rand(1, 3),
                'is_round_trip' => rand(0, 1),
                'estimated_price' => round(($route['dist'] * 1.8 + 8.0) * 1.20, 2),
                'expires_at' => now()->addHours(24),
                'created_at' => now()->subHours(rand(1, 12)),
            ]);
        }

        $this->command->info('Demo data seeded successfully:');
        $this->command->info('  - '.count($clients).' client users');
        $this->command->info('  - '.count($companies).' companies');
        $this->command->info('  - '.count($driverProfiles).' driver profiles ('.count($verifiedDriversArr).' verified)');
        $this->command->info('  - '.count($ridesCreated).' rides');
        $this->command->info('  - '.Payment::count().' payments');
        $this->command->info('  - '.Review::count().' reviews');
    }
}
