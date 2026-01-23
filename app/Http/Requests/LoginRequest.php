<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'device_name'=> 'required',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
