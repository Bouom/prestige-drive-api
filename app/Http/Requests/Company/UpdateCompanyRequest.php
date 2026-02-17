<?php

namespace App\Http\Requests\Company;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = Company::where('uuid', $this->route('uuid'))->first();

        if (! $company) {
            return false;
        }

        return $this->user() && (
            $this->user()->companies()->where('companies.id', $company->id)->exists() ||
            $this->user()->isAdmin()
        );
    }

    public function rules(): array
    {
        $company = Company::where('uuid', $this->route('uuid'))->first();
        $companyId = $company?->id;

        return [
            // Company Information
            'legal_name' => ['sometimes', 'string', 'max:255'],
            'trade_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'registration_number' => ['sometimes', 'string', 'max:50', Rule::unique('companies')->ignore($companyId)],
            'vat_number' => ['sometimes', 'nullable', 'string', 'max:50'],

            // Contact Information
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('companies')->ignore($companyId)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'website' => ['sometimes', 'nullable', 'string', 'max:255', 'url'],

            // Address
            'address' => ['sometimes', 'string', 'max:500'],
            'postal_code' => ['sometimes', 'string', 'max:20'],
            'city' => ['sometimes', 'string', 'max:100'],
            'country' => ['sometimes', 'string', 'max:100'],

            // Legal Representative
            'representative_name' => ['sometimes', 'string', 'max:255'],
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
            'registration_number.unique' => 'This registration number is already registered.',
            'email.unique' => 'This email address is already in use.',
            'email.email' => 'Please provide a valid email address.',
            'website.url' => 'Please provide a valid website URL.',
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
