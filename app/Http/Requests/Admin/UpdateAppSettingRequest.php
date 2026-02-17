<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only super admins can update app settings
        return $this->user() && $this->user()->can('manage-app-settings');
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:100'],
            'value' => ['required'],
            'type' => ['required', 'in:string,integer,boolean,json,float'],
            'group' => ['sometimes', 'nullable', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'Setting key is required.',
            'value.required' => 'Setting value is required.',
            'type.required' => 'Setting type is required.',
            'type.in' => 'Setting type must be string, integer, boolean, json, or float.',
        ];
    }

    /**
     * Additional validation after basic rules
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate value matches the specified type
            $type = $this->input('type');
            $value = $this->input('value');

            switch ($type) {
                case 'integer':
                    if (! is_numeric($value) || (int) $value != $value) {
                        $validator->errors()->add('value', 'Value must be an integer.');
                    }
                    break;
                case 'boolean':
                    if (! in_array($value, [true, false, 0, 1, '0', '1'], true)) {
                        $validator->errors()->add('value', 'Value must be a boolean.');
                    }
                    break;
                case 'float':
                    if (! is_numeric($value)) {
                        $validator->errors()->add('value', 'Value must be a number.');
                    }
                    break;
                case 'json':
                    if (is_string($value)) {
                        json_decode($value);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $validator->errors()->add('value', 'Value must be valid JSON.');
                        }
                    }
                    break;
            }
        });
    }
}
