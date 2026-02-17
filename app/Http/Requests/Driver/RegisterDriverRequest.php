<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class RegisterDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated and not already a driver
        return $this->user() && ! $this->user()->driverProfile;
    }

    public function rules(): array
    {
        return [
            // License Information
            'license_number' => ['required', 'string', 'max:50', 'unique:driver_profiles,license_number'],
            'license_type_id' => ['required', 'integer', 'exists:license_types,id'],
            'license_issued_at' => ['sometimes', 'nullable', 'date', 'before:today'],
            'license_expires_at' => ['required', 'date', 'after:today'],

            // Professional Information
            'professional_card_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:100'],

            // Employment Type
            'employment_type' => ['required', 'in:independent,company_employed'],
            'company_id' => ['required_if:employment_type,company_employed', 'nullable', 'integer', 'exists:companies,id'],

            // Vehicle Preferences
            'max_passengers' => ['required', 'integer', 'min:1', 'max:8'],
            'accepts_shared_rides' => ['sometimes', 'boolean'],

            // Banking Information (required for payouts)
            'iban' => ['required', 'string', 'size:34', 'regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/'],
            'bic' => ['sometimes', 'nullable', 'string', 'size:11', 'regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/'],
            'bank_account_holder' => ['required', 'string', 'max:255'],

            // Bio
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'languages' => ['sometimes', 'nullable', 'array'],
            'languages.*' => ['string', 'size:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'license_number.required' => 'License number is required.',
            'license_number.unique' => 'This license number is already registered.',
            'license_type_id.required' => 'Please select a license type.',
            'license_type_id.exists' => 'The selected license type is invalid.',
            'license_expires_at.required' => 'License expiration date is required.',
            'license_expires_at.after' => 'License must be valid (not expired).',
            'years_experience.required' => 'Please specify your years of driving experience.',
            'years_experience.min' => 'Years of experience cannot be negative.',
            'employment_type.required' => 'Please select your employment type.',
            'employment_type.in' => 'Invalid employment type selected.',
            'company_id.required_if' => 'Company is required for company-employed drivers.',
            'max_passengers.required' => 'Please specify maximum number of passengers.',
            'max_passengers.min' => 'At least 1 passenger must be allowed.',
            'max_passengers.max' => 'Maximum 8 passengers allowed.',
            'iban.required' => 'IBAN is required for driver payouts.',
            'iban.size' => 'IBAN must be 34 characters.',
            'iban.regex' => 'Invalid IBAN format.',
            'bic.size' => 'BIC must be 11 characters.',
            'bic.regex' => 'Invalid BIC format.',
            'bank_account_holder.required' => 'Bank account holder name is required.',
            'languages.*.size' => 'Each language code must be 2 characters.',
        ];
    }
}
