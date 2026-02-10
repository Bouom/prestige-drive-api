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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers(),
            ],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'manager_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_zip_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'company_city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'driver_count' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'company_iban' => ['sometimes', 'nullable', 'string', 'max:34'],
            'bic_code' => ['sometimes', 'nullable', 'string', 'max:11'],
            'is_available' => ['sometimes', 'nullable', 'boolean'],
            'license_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'experience' => ['sometimes', 'nullable', 'string', 'max:255'],
            'insurance_issue_date' => ['sometimes', 'nullable', 'date'],
            'insurance_expiry_date' => ['sometimes', 'nullable', 'date'],
            'id_issue_date' => ['sometimes', 'nullable', 'date'],
            'id_expiry_date' => ['sometimes', 'nullable', 'date'],
            'license_issue_date' => ['sometimes', 'nullable', 'date'],
            'license_expiry_date' => ['sometimes', 'nullable', 'date'],
            'pro_card_issue_date' => ['sometimes', 'nullable', 'date'],
            'pro_card_expiry_date' => ['sometimes', 'nullable', 'date'],
        ];
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
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.required' => 'Le rôle est obligatoire.',
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
            'role' => 'rôle',
            'password' => 'mot de passe',
            'password_confirmation' => 'confirmation du mot de passe',
        ];
    }
}
