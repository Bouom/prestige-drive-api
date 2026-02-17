<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class RespondToReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only the driver who was reviewed can respond
        $review = $this->route('review');

        return $this->user() &&
               $review &&
               $this->user()->id === $review->reviewee_id &&
               ! $review->driver_response; // Can only respond once
    }

    public function rules(): array
    {
        return [
            'driver_response' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'driver_response.required' => 'Response text is required.',
            'driver_response.min' => 'Response must be at least 10 characters.',
            'driver_response.max' => 'Response must not exceed 500 characters.',
        ];
    }
}
