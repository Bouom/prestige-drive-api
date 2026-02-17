<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Company Information
            'legal_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:50', 'unique:companies,registration_number'],
            'vat_number' => ['sometimes', 'nullable', 'string', 'max:50'],

            // Contact Information
            'email' => ['required', 'string', 'email', 'max:255', 'unique:companies,email'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'website' => ['sometimes', 'nullable', 'string', 'max:255', 'url'],

            // Address
            'address' => ['required', 'string', 'max:500'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['sometimes', 'string', 'max:100'],

            // Legal Representative
            'representative_name' => ['required', 'string', 'max:255'],
            'representative_position' => ['sometimes', 'nullable', 'string', 'max:100'],

            // Financial Information
            'iban' => ['sometimes', 'nullable', 'string', 'size:34', 'regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/'],
            'bic' => ['sometimes', 'nullable', 'string', 'size:11', 'regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/'],
            'billing_email' => ['sometimes', 'nullable', 'string', 'email', 'max:255'],

            // Additional Info
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'legal_name.required' => 'Company legal name is required.',
            'registration_number.required' => 'Company registration number is required.',
            'registration_number.unique' => 'This registration number is already registered.',
            'email.required' => 'Company email address is required.',
            'email.unique' => 'This email address is already in use.',
            'email.email' => 'Please provide a valid email address.',
            'website.url' => 'Please provide a valid website URL.',
            'address.required' => 'Company address is required.',
            'postal_code.required' => 'Postal code is required.',
            'city.required' => 'City is required.',
            'representative_name.required' => 'Legal representative name is required.',
            'iban.size' => 'IBAN must be 34 characters.',
            'iban.regex' => 'Invalid IBAN format.',
            'bic.size' => 'BIC must be 11 characters.',
            'bic.regex' => 'Invalid BIC format.',
            'billing_email.email' => 'Please provide a valid billing email address.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be a file of type: jpeg, png, jpg, gif, webp.',
            'logo.max' => 'Logo size must not exceed 2MB.',
        ];
    }
}
