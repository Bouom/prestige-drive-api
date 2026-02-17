<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            // Driver documents
            [
                'name' => 'driving_license',
                'display_name' => 'Permis de conduire',
                'description' => 'Permis de conduire valide',
                'applies_to' => ['driver'],
                'is_required' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'is_active' => true,
            ],
            [
                'name' => 'vtc_card',
                'display_name' => 'Carte professionnelle VTC',
                'description' => 'Carte professionnelle de conducteur VTC',
                'applies_to' => ['driver'],
                'is_required' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'is_active' => true,
            ],
            [
                'name' => 'id_card',
                'display_name' => 'Carte d\'identité / Passeport',
                'description' => 'Pièce d\'identité valide',
                'applies_to' => ['driver'],
                'is_required' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'is_active' => true,
            ],
            [
                'name' => 'criminal_record',
                'display_name' => 'Extrait de casier judiciaire',
                'description' => 'Bulletin n°3 du casier judiciaire (moins de 3 mois)',
                'applies_to' => ['driver'],
                'is_required' => true,
                'requires_expiry_date' => false,
                'requires_document_number' => false,
                'is_active' => true,
            ],
            [
                'name' => 'insurance',
                'display_name' => 'Attestation d\'assurance',
                'description' => 'Attestation d\'assurance responsabilité civile professionnelle',
                'applies_to' => ['driver'],
                'is_required' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'is_active' => true,
            ],
            [
                'name' => 'bank_details',
                'display_name' => 'RIB',
                'description' => 'Relevé d\'identité bancaire',
                'applies_to' => ['driver'],
                'is_required' => true,
                'requires_expiry_date' => false,
                'requires_document_number' => false,
                'is_active' => true,
            ],

            // Vehicle documents
            [
                'name' => 'vehicle_registration',
                'display_name' => 'Carte grise',
                'description' => 'Certificat d\'immatriculation du véhicule',
                'applies_to' => ['vehicle'],
                'is_required' => true,
                'requires_expiry_date' => false,
                'requires_document_number' => true,
                'is_active' => true,
            ],
            [
                'name' => 'technical_inspection',
                'display_name' => 'Contrôle technique',
                'description' => 'Procès-verbal de contrôle technique (moins de 6 mois)',
                'applies_to' => ['vehicle'],
                'is_required' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => false,
                'is_active' => true,
            ],
            [
                'name' => 'vehicle_insurance',
                'display_name' => 'Assurance véhicule',
                'description' => 'Attestation d\'assurance du véhicule',
                'applies_to' => ['vehicle'],
                'is_required' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'is_active' => true,
            ],

            // Company documents
            [
                'name' => 'company_registration',
                'display_name' => 'Kbis',
                'description' => 'Extrait Kbis de moins de 3 mois',
                'applies_to' => ['company'],
                'is_required' => true,
                'requires_expiry_date' => false,
                'requires_document_number' => true,
                'is_active' => true,
            ],
            [
                'name' => 'company_insurance',
                'display_name' => 'Attestation d\'assurance société',
                'description' => 'Attestation d\'assurance responsabilité civile de l\'entreprise',
                'applies_to' => ['company'],
                'is_required' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'is_active' => true,
            ],
            [
                'name' => 'company_bank_details',
                'display_name' => 'RIB société',
                'description' => 'Relevé d\'identité bancaire de la société',
                'applies_to' => ['company'],
                'is_required' => true,
                'requires_expiry_date' => false,
                'requires_document_number' => false,
                'is_active' => true,
            ],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }

        $this->command->info('Document types seeded successfully.');
    }
}
