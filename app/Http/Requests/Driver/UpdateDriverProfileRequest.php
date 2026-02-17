<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated and have a driver profile
        return $this->user() && $this->user()->driverProfile;
    }

    public function rules(): array
    {
        $driverProfileId = $this->user()->driverProfile->id;

        return [
            // License Information
            'license_number' => ['sometimes', 'string', 'max:50', Rule::unique('driver_profiles')->ignore($driverProfileId)],
            'license_type_id' => ['sometimes', 'integer', 'exists:license_types,id'],
            'license_issued_at' => ['sometimes', 'nullable', 'date', 'before:today'],
            'license_expires_at' => ['sometimes', 'date', 'after:today'],

            // Professional Information
            'professional_card_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'years_experience' => ['sometimes', 'integer', 'min:0', 'max:100'],

            // Vehicle Preferences
            'max_passengers' => ['sometimes', 'integer', 'min:1', 'max:8'],
            'accepts_shared_rides' => ['sometimes', 'boolean'],

            // Banking Information
            'iban' => ['sometimes', 'string', 'size:34', 'regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/'],
            'bic' => ['sometimes', 'nullable', 'string', 'size:11', 'regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/'],
            'bank_account_holder' => ['sometimes', 'string', 'max:255'],

            // Bio and Languages
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'languages' => ['sometimes', 'nullable', 'array'],
            'languages.*' => ['string', 'size:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'license_number.unique' => 'This license number is already registered.',
            'license_type_id.exists' => 'The selected license type is invalid.',
            'license_expires_at.after' => 'License must be valid (not expired).',
            'years_experience.min' => 'Years of experience cannot be negative.',
            'max_passengers.min' => 'At least 1 passenger must be allowed.',
            'max_passengers.max' => 'Maximum 8 passengers allowed.',
            'iban.size' => 'IBAN must be 34 characters.',
            'iban.regex' => 'Invalid IBAN format.',
            'bic.size' => 'BIC must be 11 characters.',
            'bic.regex' => 'Invalid BIC format.',
            'languages.*.size' => 'Each language code must be 2 characters.',
        ];
    }
}
