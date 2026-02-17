<?php

namespace Database\Seeders;

use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VehicleBrandAndModelSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DATA: BRANDS (Old ID => Name)
        $brands = [
            1 => 'BMW', 2 => 'Peugeot', 3 => 'Renault', 4 => 'Mercedes-Benz', 6 => 'Abarth',
            7 => 'Aiways', 8 => 'Alfa Romeo', 10 => 'Alpine', 11 => 'Artega', 12 => 'Aston Martin',
            14 => 'Audi', 15 => 'Bentley', 16 => 'BMW Alpina', 19 => 'Cadillac', 20 => 'Caterham',
            21 => 'Chevrolet', 22 => 'Chrysler', 23 => 'Citroën', 24 => 'Cupra', 25 => 'Dacia',
            26 => 'Daihatsu', 27 => 'Dodge', 28 => 'Donkervoort', 29 => 'DS', 30 => 'Ferrari',
            31 => 'Fiat', 32 => 'Ford', 33 => 'Genesis', 34 => 'Honda', 35 => 'Hummer',
            36 => 'Hyundai', 37 => 'Infiniti', 38 => 'Isuzu', 39 => 'Jaguar', 40 => 'Jeep',
            41 => 'KIA', 42 => 'KTM', 43 => 'Lada', 44 => 'Lamborghini', 45 => 'Lancia',
            46 => 'Land Rover', 48 => 'Lexus', 49 => 'Lotus', 50 => 'Lynk & Co', 53 => 'Maserati',
            54 => 'Mazda', 55 => 'McLaren', 57 => 'MG', 58 => 'Mia Electric', 60 => 'MINI',
            61 => 'Mitsubishi', 62 => 'Nissan', 63 => 'Opel', 65 => 'Polestar', 66 => 'Porsche',
            68 => 'Rolls-Royce', 69 => 'Saab', 70 => 'Seat', 71 => 'Seres', 72 => 'Skoda',
            73 => 'Smart', 74 => 'Ssangyong', 75 => 'Subaru', 76 => 'Suzuki', 77 => 'Tesla',
            78 => 'Toyota', 79 => 'Volkswagen', 80 => 'Volvo', 81 => 'Aleko', 82 => 'Aro',
            83 => 'Asia', 84 => 'Austin', 85 => 'AutoBianchi', 86 => 'Auverland', 87 => 'BedFord',
            88 => 'Bertone', 89 => 'Bollore', 90 => 'Buick', 91 => 'Corvette', 92 => 'Daewoo',
            93 => 'Daf', 94 => 'Dallas', 95 => 'Datsun', 96 => 'Fso', 97 => 'Fuso',
            98 => 'Gac Gonow', 99 => 'Grandin', 100 => 'GME', 101 => 'HotchKiss', 102 => 'Innocenti',
            103 => 'Iveco', 104 => 'LDV', 105 => 'Mahindra', 106 => 'Maruti', 107 => 'Matra',
            108 => 'Mega', 109 => 'MGF', 110 => 'Morgan', 111 => 'Panhard', 112 => 'PGO',
            113 => 'Piaggio', 114 => 'Polski-Fso', 115 => 'Pontiac', 116 => 'Proton', 117 => 'Santana',
            118 => 'SimCa', 119 => 'Talbot', 120 => 'Tata', 121 => 'Tavria', 122 => 'Teilhol',
            123 => 'Think', 124 => 'Triumph', 125 => 'UMM', 126 => 'Willys', 127 => 'Zastava',
            129 => 'Rover',
        ];

        // Lookup array to store [Old SQL ID => New DB ID]
        $brandIdLookup = [];

        foreach ($brands as $oldId => $name) {
            $brand = VehicleBrand::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            );
            $brandIdLookup[$oldId] = $brand->id;
        }

        // 2. DATA: MODELS (Brand Old ID => [Model Names])
        // Simplified grouped by brand for cleaner seeding logic
        $modelsData = [
            6 => ['500', '124 Spider', 'Grande Punto', 'Punto Evo'],
            7 => ['U5'],
            8 => ['Giulia', 'Stelvio', '145', '146', '147', '155', '156', '159', '164', '166', '33', '4C', '75', '8C', 'Brera', 'Crosswagon', 'Giulietta', 'GT', 'GTV', 'MiTo', 'Spider', '156 SW', '159 SW', 'SUD', 'Alfetta'],
            10 => ['A110', 'A310', 'A610', 'V6'],
            11 => ['GT Coupé'],
            12 => ['DB11', 'DBx', 'DBS Superleggera', 'Vantage', 'DB7', 'DB9', 'DBS', 'Rapide', 'V12 Vantage', 'V8', 'V8 Vantage', 'Vanquish', 'Virage', 'Volante', 'Cygnet'],
            14 => ['A1', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q2', 'Q3', 'Q4', 'Q5', 'Q7', 'Q8', 'E-tron', 'E-tron GT', 'R8', 'TT', '100', '80', 'A2', 'RS Q3', 'SQ7', 'V8', 'TRS Coupé', 'TTS Coupé', '200', 'A3 Cabriolet', 'A3 SportBack', 'A4 AllRoad', 'A4 Cabriolet', 'A4 lll-lV', 'A4 V', 'A5 Cabriolet', 'A5 SportBack', 'A6lV', 'A7 SportBack', 'All Road', 'All Road Quattro', 'Cabriolet', 'Coupé', '90', 'Quattro', 'RS3', 'RS3 SportBack', 'RS4', 'RS5', 'RS6', 'RS7', 'S1', 'S3', 'S3 SportBack', 'S4', 'S4 Cabriolet', 'S5', 'S6', 'S7', 'S8'],
            15 => ['Bentayga', 'Flying Spur', 'Continental GT', 'Mulsanne', 'Arnage', 'Azure', 'Berline', 'Brooklands', 'Continental', 'Continental Flying Spur'],
            1 => ['i3', 'l4', 'lx', 'lx3', 'Série 1', 'Série 2', 'Série 3', 'Série 4', 'Série 5', 'Série 6', 'Série 7', 'Série 8', 'X1', 'X2', 'X3', 'X4', 'X5', 'X6', 'X7', 'Z4', 'i8', 'Z3', 'Z8', '116', '118', '120', '123', '125', '130', '135', '315', '316', '318', '320', '323', '324', '325', '328', '330', '335', '518', '520', '523', '524', '525', '528', '530', '535', '540', '545', '550', '628', '630', '635', '640', '645', '650', '725', '728', '730', '732', '735', '740', '745', '750', '760', '840', '850', 'M1', 'M3', 'M4', 'M5', 'M535', 'M6', 'M635', 'Z1'],
            16 => ['B10', 'B12', 'B12 L', 'B3', 'B4', 'B5', 'B6', 'B7', 'D3', 'D4', 'D5', 'Roadster S', 'Roadster V8', 'XD3'],
            19 => ['ATS', 'BLS', 'CTS', 'Eldorado', 'Escalade', 'Seville', 'SRX', 'STS', 'XLR', 'BLS Wagon', 'CTS-V'],
            20 => ['Super 7'],
            21 => ['Alero', 'Astro', 'Avero', 'Beretta', 'Blazer', 'Camaro', 'Captiva', 'Corsica', 'Corvette', 'Corvette C6', 'Cruze', 'Epica', 'Evanda', 'HHR', 'Kalos', 'Lacetti', 'Lumina', 'Malibu', 'Matiz', 'Nubira', 'Orlando', 'Spark', 'Tacuma', 'Tahoe', 'TrailBlazer', 'Trans Sport', 'Trax', 'Volt'],
            23 => ['Berlingo', 'C1', 'C3', 'C3 Aircross', 'C4', 'C5 Aircross', 'Grand C4 Spacetourer', 'Jumpy', 'Spacetourer', 'AX', 'C-Elysée', 'C-Zero', 'C-Crosser', 'C15', 'C2', 'C3 Picasso', 'C3 Pluriel', 'C4 Aircross', 'C4 Picasso', 'C4 Spacetourer', 'C5', 'C6', 'C8', 'DS3', 'DS4', 'DS5', 'E-Mehari', 'Evasion', 'Grand C4 Picasso', 'Nemo', 'Saxo', 'Xantia', 'XM', 'Xsara', 'Xsara Picasso', 'ZX', '2CV', 'Acadiane', 'Ami', 'Axel', 'Berlingo ll', 'BX', 'C2 Entreprise', 'C25', 'C25 Combi', 'C3 Entreprise', 'C3 ll', 'C35', 'C4 Cactus', 'C4 Entreprise', 'C4 ll', 'C5 ll', 'C4 Picasso ll', 'C5 ll Tourer', 'Dangel C15', 'Dangel C25', 'CX', 'DS', 'Evasion VSX', 'GS', 'GSA', 'Dyane', 'HY', 'ID', 'Jumper', 'Katar', 'LN', 'LNA', 'Nemo Combi', 'Rosalie', 'Traction', 'Visa'],
            24 => ['Ateca', 'Formentor', 'Leon'],
            25 => ['Duster', 'Sandero', 'Lodgy', 'Spring', 'Dokker', 'Logan', 'Logan MCV', 'Sandero Stepway'],
            26 => ['Applause', 'Charade', 'Cuore', 'Copen', 'Domino', 'Feroza', 'Grand Move', 'Materia', 'Move', 'Rocky', 'Sirion', 'Terios', 'Trevis', 'YRV', 'Hijet', 'Leeza'],
            27 => ['Avenger', 'Viper', 'Caliber', 'Journey', 'Nitro'],
            28 => ['D8', 'Donkervoort'],
            29 => ['DS3', 'DS4', 'DS5', 'DS7', 'DS9'],
            30 => ['812', 'F 488', 'F 8', 'GTC 4', 'Portofino', 'Monza', 'Roma', 'Sf90', 'California', 'F 12', 'F 355', 'F 360', 'F 430', 'F 456', 'F 458', 'F 50', 'F 512', 'F 575', 'F 599', 'F 612', 'FF', 'Superamerica', '328'],
            31 => ['500', '500L', '500X', 'Panda', 'Tipo', '124 Spider', '126', 'Barchetta', 'Brava', 'Bravo', 'Cinquecento', 'Coupé', 'Croma', 'Doblo', 'Fiorino', 'Freemont', 'Idea', 'Marea', 'Multipla', 'Palio', 'Punto', 'Qubo', 'Scudo', 'Sedici', 'Seicento', 'Stilo', 'Tempra', 'Ulysse', 'Uno', '127', '128', '131', '132', '238', '4x4 Cross', '500C', '600', '850', '900', 'Argenta', 'Bravo ll', 'Doblo Cargo', 'Ducato', 'Grande Punto', 'PininFarina', 'Punto Evo', 'Regata', 'Ritmo', 'Stilo Commerciale', 'Stilo Multiwagon', 'Stilo UpRoad', 'Strada', 'X1/9'],
            32 => ['Ecosport', 'Edge', 'Explorer', 'Fiesta', 'Focus', 'Galaxy', 'Kuga', 'Mondeo', 'Mustang', 'Mustang mach-e', 'Puma', 'S-Max', 'Tourneo', 'Tourneo Custom', 'AeroStar', 'B-Max', 'Bronco ll', 'C-Max', 'Capri', 'Compact', 'Cougar', 'Courrier', 'Escort', 'Focus C-Max', 'Focus Coupé Cabriolet', 'Fusion', 'Granada', 'Jeep', 'KA', 'Maverick', 'Orion', 'P100', 'Probe', 'Scorpio', 'Sierra', 'Sportika', 'Street KA', 'Taunus', 'Tourneo Connect', 'Tourneo Courrier', 'Transit', 'Transit Connect', 'Transit Courrier', 'Transit Custom', 'Transit Vl'],
            34 => ['Civic', 'CR-V', 'Honda e', 'HR-V', 'Jazz', 'Accord', 'Concerto', 'CR-Z', 'CRX', 'FR-V', 'Insight', 'Integra', 'Legend', 'Logo', 'New', 'NSX', 'Prelude', 'S 2000', 'Shuttle', 'Stream', 'Civic AeroDeck', 'Quintet'],
            36 => ['Bayon', 'i10', 'i20', 'i30', 'Ioniq', 'Ioniq 5', 'Kona', 'Nexo', 'Santa Fe', 'Tucson', 'Accent', 'Atos', 'Atos Prime', 'Azera', 'Coupé', 'Elantra', 'Excel', 'Galloper', 'Genesis', 'Genesis Coupé', 'Getz', 'Grand Santa Fe', 'Grandeur', 'H-1 People', 'i40', 'ix20', 'ix35', 'ix55', 'Lantra', 'Matrix', 'Pony', 'Satellite', 'Scoupé', 'Sonata', 'Terracan', 'Trajet', 'Veloster', 'XG', 'Accent Société', 'Getz Société', 'H1', 'H1 Van', 'H100', 'l10 Société', 'l20 Société', 'l30 Société', 'l30 CW', 'ix20', 'Matrix Société', 'Terracan Société'],
            35 => ['H2', 'H3', 'H1'],
            22 => ['300M', '300C', 'Crossfire', 'Grand', 'Grand Voyager', 'Le Baron', 'Neon', 'New Yorker', 'PT Cruiser', 'Sebring', 'Sebring Conversible', 'Stratus', 'Viper', 'Vision', 'Voyager', 'PT Cruiser Cabrio', 'Saratoga'],
            37 => ['FX', 'EX37', 'G37', 'M', 'Q30', 'Q50', 'Q60 Coupé', 'Q70', 'QX30', 'QX50', 'QX70'],
            38 => ['D-Max', 'Série N', 'Trooper'],
            39 => ['E-pace', 'F-pace', 'F-Type', 'I-pace', 'XE', 'XF', 'Oldtimer', 'S-Type', 'X-Type', 'XJ', 'XJ-S', 'XK', 'Daimler', 'Sovereign', 'X-Type Estate', 'XJ6', 'XJ8', 'XK8', 'XJ-R', 'XK-R'],
            40 => ['Compass', 'Renegade', 'Wrangler', 'Cherokee', 'Commander', 'Grand', 'Grand Cherokee', 'Patriot'],
            41 => ['Ev6', 'Ceed', 'Niro', 'Picanto', 'Pro Ceed', 'Rio', 'Sorento', 'Soul', 'Sportage', 'Stinger', 'Stonic', 'Xceed', 'Carens', 'Carnival', 'Cerato', 'Clarus', 'Magentis', 'Opirus', 'Optima', 'Pride', 'Retona', 'Sephia', 'Shuma', 'Venga', 'Besta', 'Ceed SW', 'K2500', 'K2700', 'Pregio', 'Sephia ll', 'Shuma ll'],
            42 => ['X-Bow'],
            43 => ['110', '111', '112', '4x4', 'Classica', 'Diva', 'Granta', 'Kalina', 'Niva', 'Priora', 'Samara', '1200', '1300', '1500', '1600', '2104', '2107', 'Kalinka', 'Natacha', 'Sagona'],
            44 => ['Aventador', 'Huracan', 'Urus', 'Diablo', 'Gallardo', 'Murciélago'],
            45 => ['Dedra', 'Delta', 'Flavia', 'Kappa', 'Lybra', 'Musa', 'Phedra', 'Thema', 'Thesis', 'Voyager', 'Y', 'Y 10', 'Ypsilon', 'Zeta', 'Beta', 'Gamma', 'HP Executive', 'Prisma'],
            46 => ['Defender', 'Discovery', 'Discovery Sport', 'Range Rover', 'Range Rover Evoque', 'Range Rover Sport', 'Range Rover Velar', 'Defender Pick-Up', 'Freelander', 'Range', '88', '90', '109', '110', 'Range 4x4'],
            48 => ['ES', 'LC', 'LS', 'NX', 'RX', 'UX', 'CT', 'GS', 'IS', 'RC', 'SC', 'IS 200', 'IS 220', 'IS 250', 'IS 300', 'IS F', 'RX 300', 'RX 350'],
            49 => ['Elise', 'Exige', 'Evora', '2-Eleven', 'Elan', 'Esprit', 'Europa'],
            50 => ['01'],
            53 => ['Ghibli', 'Levante', 'Mc20', 'Quattroporte', 'Cabriolet', 'Coupé', 'GranCabrio', 'GranTurismo', 'Spyder', '3200 GT', 'GranSport Coupé', 'GranSport Spyder'],
            54 => ['CX-3', 'CX-30', 'CX-5', 'Madza2', 'Madza3', 'Madza6', 'MX-30', 'MX-5', '121', '323', '626', 'CX-7', 'Demio', 'Madza5', 'MPV', 'MX-3', 'MX-6', 'Premacy', 'RX-7', 'RX-8', 'Tribute', 'Xedos 6', 'Xedos 9', 'Bongo', '929', 'BT 50', 'E2200', 'Mazda6 FastWagon', 'MiniBus'],
            55 => ['600LT', '720S', 'Artura', 'GT', '540C', '570GT', '570S', '650S', '765LT', 'MP4-12C'],
            4 => ['Classe A', 'Classe B', 'Classe C', 'Classe CLA', 'Classe CLS', 'Classe E', 'Classe G', 'Classe GLA', 'Classe GLC', 'Classe GLE', 'Classe GLS', 'Classe S', 'Classe V', 'EQA', 'EQC', 'EQV', 'GLB', 'GT AMG', '190', 'Citan', 'Classe CL', 'Classe CLC', 'Classe CLK', 'Classe GL', 'Classe GLK', 'Classe M', 'Classe R', 'Classe SL', 'Classe SLC', 'Classe SLK', 'SLR-McLaren', 'SLS AMG', 'Vaneo', 'Viano', '200', '220', '230', '240', '250', '260', '280', '300', '320', '350', '380', '400', '420', '450', '4x4', '500', '560', '600', 'A140', 'A140 Business', 'A150', 'A160', 'A160 Business', 'A170', 'A170 Business', 'A180', 'A190', 'A190 Business', 'A200', 'A210', 'B150', 'B160', 'B170', 'B180', 'B200', 'B220', 'B250', 'C160 Coupé', 'C180', 'C180 Coupé', 'C200', 'C200 Coupé', 'C220', 'C220 Coupé', 'C230', 'C230 Coupé', 'C240', 'C250', 'C270', 'C280', 'C300', 'C320', 'C320 Coupé', 'C350', 'C350 Coupé', 'C AMG', 'C AMG Coupé', 'CL', 'CL 500', 'CL 600', 'CL AMG', 'Classe C lll', 'Classe C lV', 'Classe E lV', 'Classe E V', 'E200', 'E220', 'E230', 'E240', 'E250', 'E270', 'E280', 'E300', 'E320', 'E350', 'E400', 'E420', 'E430', 'E500', 'E AMG', 'G230', 'G270', 'G290', 'G300', 'G320', 'G350', 'G400', 'G500', 'G AMG', 'GL', 'GT', 'M230', 'M250', 'M270', 'M280', 'M300', 'M320', 'M350', 'M400', 'M420', 'M430', 'M450', 'M500', 'MB100', 'ML AMG', 'R280', 'R300', 'R320', 'R350', 'R500', 'R AMG', 'S250', 'S280', 'S300', 'S320', 'S350', 'S400', 'S420', 'S430', 'S450', 'S500', 'S600', 'S AMG', 'SL280', 'SL300', 'SL320', 'SL350', 'SL500', 'SL600', 'SL AMG', 'SLK', 'Sprinter', 'T1', 'T2', 'V220', 'V230', 'V280', 'Vaneo Business', 'Vito', 'Vito F'],
            57 => ['Hs', 'EHF', 'MG-TF', 'MGF', 'ZR', 'ZS', 'ZS EV', 'ZT', 'Midget', 'B', 'Roadster', 'ZT T'],
            58 => ['Mia L'],
            60 => ['Countryman', 'Paceman', 'Mini', 'Mini Cabriolet', 'Mini Cooper', 'Mini ClubMan'],
            61 => ['ASX', 'Eclipse Cross', 'Outlander', 'Space Star', '3000 GT', 'Attrage', 'Carisma', 'Colt', 'Eclipse', 'Galant', 'Grandis', 'i-MiEv', 'L200', 'Lancer', 'Lancer EVO', 'Lancer Evolution', 'Pajero', 'Pajero Pinin', 'Sigma', 'Space Gear', 'Space Runner', 'Space Wagon', 'Valley', 'Canter', 'Colt CZ3', 'Colt CZC', 'Sapporo', 'Space Star ll'],
            62 => ['Evalia', 'GT-R', 'Juke', 'Leaf', 'Micra', 'Qashqai', 'X-TRAIL', '100', '100NX', '200', '200SX', '300ZX', '350Z', '370Z', 'Almera', 'Almera Tino', 'Combi-8', 'Cube', 'Maxima', 'Maxima QX', 'Murano', 'Navara', 'Note', 'NV200', 'Pathfinder', 'Patrol', 'Patrol GR', 'Pick-Up', 'Pixo', 'Prairie', 'Primastar', 'Primera', 'Pulsar', 'Qashqai+2', 'Serena', 'Sunny', 'Terrano', 'Terrano ll', 'Vanette', '280ZX', 'Almera Sedan', 'Atleon', 'BlueBird', 'Cab Star', 'Cedric', 'Ebro', 'Eco T', 'InterStar', 'InterStar Combi', 'King Cab', 'KubiStar', 'L35', 'Micra C+C', 'NP300', 'NT400 CabStar', 'NV400', 'Patrol 4x4', 'Primastar Avantour', 'Primastar Combi', 'Silvia', 'Stanza', 'Trade', 'Urvan'],
            63 => ['Astra', 'Combo', 'Corsa', 'Crossland', 'Grandland X', 'Insignia', 'Mokka', 'Zafira', 'Adam', 'Agila', 'Ampera', 'Ampera-e', 'Antara', 'Calibra', 'Campo', 'Cascada', 'Crossland X', 'Frontera', 'GT', 'Kadette', 'Karl', 'Mevira', 'Mokka X', 'Monterey', 'Omega', 'Senator', 'Signum', 'Sintra', 'Speedster', 'Tigra', 'Tigra TwinTop', 'Vectra', 'Vivaro', 'Zafira Tourer', 'Ascona', 'Astra GTC', 'Astra TwinTop', 'Cabrio', 'Combo Cargo', 'Corsa Van', 'Manta', 'Monza', 'Movano', 'Rekord', 'Vectra GTS', 'Vivaro Combi', 'Vivaro Tour'],
            2 => ['108', '2008', '208', '3008', '308', '5008', '508', 'Rifter', 'Traveller', '1007', '106', '107', '205', '206', '206 CC', '206+', '207', '306', '307', '307 CC', '307 SW', '309', '4007', '4008', '405', '406', '407', '407 Coupé', '508 RXH', '605', '607', '806', '807', 'Bipper', 'Expert', 'iOn', 'Partner', 'RCZ', '104', '204', '206 SW', '207 Affaire', '207 SW', '207 CC', '304', '305', '307 Affaire', '307 Break', '308 Affaire', '308 Break', '308 CC', '308 SW', '403', '404', '407 SW', '504', '505', '604', '806 SV', '807 Affaire', 'Bipper Tepee', 'Boxer', 'Expert Tepee', 'J5 Utilitaire', 'J7', 'J9', 'P4', 'Partner ll', 'Partner Tepee'],
            65 => ['1', '2'],
            66 => ['718', '911', 'Cayenne', 'Macan', 'Panamera', 'Taycan', '918', '928', '944', '968', 'Boxster', 'Carrera', 'Carrera GT', 'Cayman', '356', '912', '914', '924', '930'],
            3 => ['Arkana', 'Captur', 'Clio', 'Espace', 'Grand Scénic', 'Kadjar', 'Kangoo', 'Koleos', 'Mégane', 'Scénic', 'Talisman', 'Trafic', 'Twingo', 'Zoe', '19', '21', '25', 'Avantime', 'Cabriolet', 'Express', 'Fluence', 'Grand', 'Grand Espace', 'Grand Kangoo', 'Grand Modus', 'Kangoo Be Bop', 'Laguna', 'Latitude', 'Modus', 'Safrane', 'Scénic RX4', 'Scénic Xmod', 'Spider', 'Super 5', 'Twizy', 'Vel Satis', 'Wind', '4 CV', 'B110', 'B120', 'B70', 'B80', 'B90', 'Caravelle', 'Cherokee', 'Clio Baccara', 'Clio Estate', 'Clio Initiale', 'Clio lV Estate', 'Clio lV', 'Dauphine', 'Espace Initiale', 'Espace RXE', 'Espace V', 'EstaFette', 'Fuego', 'Grand Espace Initial', 'Grand Espace lV', 'Grand Scénic lll', 'Jeep AMC CJ7', 'Jeep Wrangler', 'JP4', 'Kangoo Express', 'Kangoo Express ll', 'Kangoo ll', 'Laguna Baccara', 'Laguna Estate', 'Laguna lll', 'Laguna lll Estate', 'Laguna Initiale', 'Mascott', 'Master', 'Master lll', 'Maxity', 'Mégane Coupé Cabriolet', 'Mégane lll', 'Mégane lll Coupé Cabriolet', 'Mégane lll Estate', 'Mégane lnitiale', 'Mégane lV', 'Messenger', 'Ondine', 'R10', 'R11', 'R12', 'R14', 'R15', 'R16', 'R17', 'R18', 'R19', 'R19 Baccara', 'R20', 'R21', 'R21 Baccara', 'R25', 'R25 Baccara', 'R30', 'R4', 'R5', 'R6', 'R8', 'R9', 'Rodeo', 'Safrane Baccara', 'Safrane Initiale', 'Saviem', 'Scénic lll', 'Spider Sport', 'Super 5 Baccara', 'Trafic lll', 'Trafic Passenger', 'Twingo lll', 'Twingo Initiale', 'Vel Satis Initiale'],
            68 => ['Cullinan', 'Dawn', 'Ghost', 'Phantom', 'Wraith', 'Berline', 'Cabriolet', 'Corniche', 'Park Ward', 'Silver Seraph', 'Silver Cloud'],
            69 => ['9-3', '9-3X', '9-4X', '9-5', '9-7X', '900', '9000', '9-3 Sport Hatch', '9-5 Estate'],
            70 => ['Alhambra', 'Ibiza', 'Arona', 'Ateca', 'Leon', 'Tarraco', 'Altea', 'Altea XL', 'Arosa', 'Cordoba', 'Exeo', 'Inca', 'Marbella', 'Mii', 'Toledo', 'Cordoba Vario', 'Fura', 'Ibiza Sport Coupé', 'Malaga', 'Ronda'],
            71 => ['Fengon 5', 'Seres 3'],
            72 => ['Enyaq', 'Fabia', 'Kamiq', 'Karoq', 'Kodiaq', 'Octavia', 'Scala', 'Superb', 'Citigo', 'Favorit', 'Felicia', 'Rapid', 'Roomster', 'Yeti', '1050', '120', '130', 'Fabia Combi', 'Fabia Sedan'],
            73 => ['Forfour', 'Fortwo', 'Roadster', 'City Coupé', 'Fortwo Cabrio', 'Fortwo Coupé', 'Roadster Coupé', 'Smart', 'Smart Business'],
            74 => ['Korando', 'Rexton', 'Tivoli', 'Actyon', 'Actyon Sports', 'Family', 'Kyron', 'Musso', 'Rodius', 'XLV'],
            75 => ['BRZ', 'Forester', 'Impreza', 'Outback', 'XV', 'B9 Tribeca', 'E', 'Justy', 'Legacy', 'Levorg', 'Mini Jumbo', 'Série L', 'SVX', 'Trezia', 'Tribeca', 'Vivio', 'Wrx', 'WRX STi', 'Justy G3X', 'Vanille'],
            76 => ['Across', 'Ignis', 'Jimmy', 'Swace', 'Swift', 'SX4 S-Cross', 'Vitara', 'Alto', 'Baleno', 'Carry', 'Celerio', 'Grand Vitara', 'Liana', 'Samurai', 'Spash', 'SX4', 'Wagon-R', 'X-90', 'Kizashi', 'Maruti', 'S-Cross', 'SJ', 'Super Carry', 'Wagon-R Plus'],
            77 => ['Model 3', 'Model S', 'Model X'],
            78 => ['Aygo', 'C-HR', 'Camry', 'Corolla', 'Highlander', 'Land Cruiser', 'Prius', 'Proace city verso', 'Proace verso', 'RAV4', 'Supra', 'Yaris', 'Yaris cross', '4-Runner', 'Auris', 'Avensis', 'Carina', 'Celica', 'Grand Prius+', 'GT86', 'Hilux', 'iQ', 'Land Cruiser V8', 'MR 2', 'MR Roadster', 'Paseo', 'Picnic', 'Previa', 'Starlet', 'Urban Cruiser', 'Verso', 'Verso-S', 'Avensis Verso', 'Carina E', 'Carina ll', 'Corolla Verso', 'Cressida', 'Crown', 'Dyna', 'Fun Cruiser', 'Hiace', 'Lexus', 'Lite ACE', 'Model F', 'Rav4 lll', 'Rav4 lV', 'Runner', 'Tercel', 'Yaris lll', 'Yaris Verso'],
            79 => ['Arteon', 'Caddy', 'Golf', 'Id.3', 'Id.4', 'Passat', 'Polo', 'Sharan', 'T-cross', 'T-roc', 'Tiguan', 'Tiguan Allspace', 'Touareg', 'Touran', 'Up!', 'Amarok', 'Beetle', 'Bora', 'Caravelle', 'CC', 'Corrado', 'EOS', 'Fox', 'Jetta', 'Lupo', 'Multivan', 'Phateon', 'Scirocco', 'Vento', '181', '183', 'Coccinelle', 'Combi', 'Crafter', 'Crafter Combi', 'Golf Plus', 'Golf SportVan', 'Golf Vl', 'Golf Vll', 'Jetta lll', 'LT28', 'LT31', 'LT32', 'LT35', 'LT40', 'LT40A', 'LT45', 'LT50', 'New Beetle', 'New Beetle Cabriolet', 'Passat CC', 'Passat SW', 'Polo V', 'Santana', 'Taro', 'Transporter', 'Transporter Shuttle'],
            80 => ['C40', 'S60', 'S90', 'V60', 'V90', 'XC40', 'XC60', 'XC90', '240', '440', '460', '480', '740', '850', '940', '960', 'C30', 'C70', 'Polar', 'S40', 'S70', 'S80', 'V40', 'V50', 'V70', 'XC70', '340', '360', '440 Société', '760', '780', '850 Gentleman', '850 Summum', '940 Société', '960 Platinum', '960 Société', '960 Summum', 'C70 Summum', 'Cross Country', 'Cross Country Summum', 'S40 Summum', 'S60 Summum', 'S70 Summum', 'S80 Summum', 'S90 Summum', 'V40 Summum', 'V70 Summum', 'V90 Summum'],
            // 33 => ['Autre'],
            81 => ['Aleko'],
            82 => ['4x4', 'Aro10', 'Carpat', 'Cross Lander', 'Forester', 'Spartana', 'Trapeurs'],
            83 => ['Rocsta'],
            // 128 => ['Autre'],
            84 => ['Allegro', 'Jeep', 'Maestro', 'Marina', 'Metro', 'Mini', 'Montego', 'Princess', 'Sherpa'],
            85 => ['A112', 'Y10'],
            86 => ['A3'],
            87 => ['CF', 'Midi'],
            // 88 => ['Autre'],
            89 => ['BlueCar', 'BlueSummer'],
            90 => ['Park Avenue'],
            91 => ['C6', 'Z06', 'ZR1', 'ZT06'],
            92 => ['Espero', 'Evanda', 'Kalos', 'Korando', 'Lacetti', 'Lanos', 'Leganza', 'Matiz', 'Musso', 'Nexia', 'Nubira', 'Rexton', 'Rezzo'],
            93 => ['400', 'VA400', 'VH400'],
            94 => ['Dallas'],
            95 => ['Cherry', 'Silvia', 'Stanza'],
            96 => ['Atou', 'Caro', 'Truck'],
            97 => ['Canter'],
            98 => ['GA200', 'WAY'],
            100 => ['Combi Minibus', 'Fourgon Midi', 'Rascal'],
            99 => ['Dallas'],
            101 => ['Jeep'],
            102 => ['120', '500', '990', 'De Tomaso', 'Mini 2', 'Mini 3'],
            103 => ['Daily', 'Daily Basic', 'Daily Classe C', 'Daily Classe S', 'Daily Classe L', 'Daily Classic', 'Massif', 'Piaggio'],
            104 => ['Convoy', 'Maxus'],
            105 => ['Bolero', 'CJ', 'GOA'],
            106 => ['800', 'Alto'],
            107 => ['Bagheera', 'M530', 'DJET'],
            108 => ['Break', 'Cabrio', 'Club', 'Concept', 'Fourgon', 'Ranch'],
            109 => ['Roadster'],
            110 => ['4/4', 'Plus 4', 'Plus 8', 'Roadster V6', 'Tourer'],
            111 => ['24', 'Dyna', 'PL'],
            112 => ['Cevennes', 'Hemera', 'Speedster ll'],
            113 => ['Porter'],
            114 => ['125', '125P', '1300', '1500', 'Caro', 'Linda', 'Mistral', 'Polonez'],
            115 => ['FireBird', 'Trans AM', 'Trans Sport'],
            116 => ['313', '315', '413', '415', '416', '418', '420'],
            117 => ['Nairobi', 'PS10', 'S300', 'S350', 'S410', 'S413', 'Samurai', 'Vitara'],
            118 => ['1000', '1100', 'Aronde', 'Sim Quatre'],
            119 => ['1510', 'Horizon', 'Matra Murena', 'Rancho', 'Samba', 'Solara', 'Tagora'],
            120 => ['TelcoLine', 'TelcoSport'],
            121 => ['1100'],
            122 => ['Rodeo', 'Tangara'],
            123 => ['City'],
            124 => ['Acclaim', 'Herald', 'SpitFire', 'STAG', 'TR3', 'TR4', 'TR6', 'TR7'],
            125 => ['Alter HardTop'],
            126 => ['Jeep'],
            127 => ['1100', '128', 'Yugo'],
            129 => ['111', '114', '115', '200', '2000', '213', '214', '215', '216', '218', '220', '2300', '2400', '25', '2600', '3500', '414', '416', '418', '420', '45', '618', '620', '623', '75', '75 Tourer', '820', '825', '827', 'Estate', 'Freight', 'MGF', 'Mini', 'Moke', 'Montego', 'Streetwise'],
        ];

        foreach ($modelsData as $oldBrandId => $names) {
            if (! isset($brandIdLookup[$oldBrandId])) {
                continue;
            }

            $newBrandId = $brandIdLookup[$oldBrandId];

            foreach ($names as $name) {
                VehicleModel::updateOrCreate(
                    [
                        'vehicle_brand_id' => $newBrandId,
                        'slug' => Str::slug($name).'-'.$newBrandId, // Append brand ID to slug to handle same model names (e.g.)
                    ],
                    [
                        'name' => $name,
                        'vehicle_class' => 'economy', // Default
                        'body_type' => 'sedan',      // Default
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
