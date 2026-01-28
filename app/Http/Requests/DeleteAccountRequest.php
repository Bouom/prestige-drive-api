<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
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
            'password' => ['required', 'string'],
            'confirmation' => ['required', 'string', 'in:DELETE,delete'],
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
            'password.required' => 'Le mot de passe est obligatoire pour supprimer le compte.',
            'confirmation.required' => 'La confirmation est obligatoire.',
            'confirmation.in' => 'Veuillez taper "DELETE" pour confirmer la suppression.',
        ];
    }
}
