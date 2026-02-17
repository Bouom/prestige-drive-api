<?php

namespace App\Http\Requests\Ride;

use Illuminate\Foundation\Http\FormRequest;

class AssignDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins or dispatchers can assign drivers
        return $this->user() && $this->user()->can('assign-drivers');
    }

    public function rules(): array
    {
        return [
            'driver_id' => [
                'required',
                'integer',
                'exists:driver_profiles,id',
                // Custom validation rule to check driver availability can be added
            ],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'driver_id.required' => 'Please select a driver to assign.',
            'driver_id.exists' => 'The selected driver does not exist.',
        ];
    }
}
