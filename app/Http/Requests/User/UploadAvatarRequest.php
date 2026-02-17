<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated to upload avatar
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048', // 2MB max
                'dimensions:min_width=100,min_height=100,max_width=4096,max_height=4096',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Please select an image to upload.',
            'avatar.image' => 'The file must be an image.',
            'avatar.mimes' => 'Avatar must be a file of type: jpeg, png, jpg, gif, webp.',
            'avatar.max' => 'Avatar size must not exceed 2MB.',
            'avatar.dimensions' => 'Avatar dimensions must be between 100x100 and 4096x4096 pixels.',
        ];
    }
}
