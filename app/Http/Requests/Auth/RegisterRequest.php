<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            // Common user fields
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'user_type' => ['sometimes', 'string', 'exists:user_types,name'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'gender' => ['sometimes', 'nullable', 'in:male,female,other'],
            'language' => ['sometimes', 'string', 'size:2'],
            'timezone' => ['sometimes', 'string', 'timezone'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'guest_token' => ['sometimes', 'nullable', 'string', 'max:36'],

            // Driver fields
            'license_type_id' => ['required_if:user_type,driver', 'nullable', 'integer', 'exists:license_types,id'],
            'license_number' => ['required_if:user_type,driver', 'nullable', 'string', 'max:50'],
            'license_issued_at' => ['sometimes', 'nullable', 'date', 'before:today'],
            'license_expires_at' => ['sometimes', 'nullable', 'date', 'after:today'],
            'professional_card_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'years_experience' => ['required_if:user_type,driver', 'nullable', 'integer', 'min:0', 'max:100'],
            'employment_type' => ['required_if:user_type,driver', 'nullable', 'in:independent,company_employed'],
            'max_passengers' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:8'],
            'iban' => ['sometimes', 'nullable', 'string', 'max:34'],
            'bic' => ['sometimes', 'nullable', 'string', 'max:11'],
            'bank_account_holder' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],

            // Driver documents
            'document_id_card' => ['required_if:user_type,driver', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_driving_license' => ['required_if:user_type,driver', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_vtc_card' => ['required_if:user_type,driver', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'id_card_issued_at' => ['sometimes', 'nullable', 'date'],
            'id_card_expires_at' => ['sometimes', 'nullable', 'date'],
            'driving_license_issued_at' => ['sometimes', 'nullable', 'date'],
            'driving_license_expires_at' => ['sometimes', 'nullable', 'date'],
            'vtc_card_issued_at' => ['sometimes', 'nullable', 'date'],
            'vtc_card_expires_at' => ['sometimes', 'nullable', 'date'],

            // Company fields
            'legal_name' => ['required_if:user_type,company', 'nullable', 'string', 'max:255'],
            'trade_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'registration_number' => ['required_if:user_type,company', 'nullable', 'string', 'max:50'],
            'vat_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'company_email' => ['required_if:user_type,company', 'nullable', 'string', 'email', 'max:255'],
            'company_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'website' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_address' => ['required_if:user_type,company', 'nullable', 'string', 'max:500'],
            'company_postal_code' => ['required_if:user_type,company', 'nullable', 'string', 'max:20'],
            'company_city' => ['required_if:user_type,company', 'nullable', 'string', 'max:100'],
            'company_country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'representative_name' => ['required_if:user_type,company', 'nullable', 'string', 'max:255'],
            'company_iban' => ['sometimes', 'nullable', 'string', 'max:34'],
            'company_bic' => ['sometimes', 'nullable', 'string', 'max:11'],
            'billing_email' => ['sometimes', 'nullable', 'string', 'email', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'driver_count' => ['sometimes', 'nullable', 'integer', 'min:1'],

            // Company documents
            'document_kbis' => ['required_if:user_type,company', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_rib' => ['sometimes', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_insurance' => ['sometimes', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'insurance_issued_at' => ['sometimes', 'nullable', 'date'],
            'insurance_expires_at' => ['sometimes', 'nullable', 'date'],
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser :max caractères.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'email' => 'adresse email',
            'password' => 'mot de passe',
            'password_confirmation' => 'confirmation du mot de passe',
        ];
    }
}
