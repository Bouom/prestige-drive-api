<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated and be the customer of the completed ride
        $ride = $this->route('ride');

        return $this->user() &&
               $ride &&
               $this->user()->id === $ride->customer_id &&
               $ride->status === 'completed';
    }

    public function rules(): array
    {
        return [
            // Overall Rating (required)
            'overall_rating' => ['required', 'numeric', 'min:1.00', 'max:5.00'],

            // Individual Ratings (optional but recommended)
            'cleanliness_rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'punctuality_rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'driving_quality_rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'professionalism_rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'vehicle_condition_rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],

            // Text Review (optional)
            'comment' => ['sometimes', 'nullable', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'overall_rating.required' => 'Overall rating is required.',
            'overall_rating.min' => 'Rating must be between 1 and 5.',
            'overall_rating.max' => 'Rating must be between 1 and 5.',
            'cleanliness_rating.min' => 'Cleanliness rating must be between 1 and 5.',
            'cleanliness_rating.max' => 'Cleanliness rating must be between 1 and 5.',
            'punctuality_rating.min' => 'Punctuality rating must be between 1 and 5.',
            'punctuality_rating.max' => 'Punctuality rating must be between 1 and 5.',
            'driving_quality_rating.min' => 'Driving quality rating must be between 1 and 5.',
            'driving_quality_rating.max' => 'Driving quality rating must be between 1 and 5.',
            'professionalism_rating.min' => 'Professionalism rating must be between 1 and 5.',
            'professionalism_rating.max' => 'Professionalism rating must be between 1 and 5.',
            'vehicle_condition_rating.min' => 'Vehicle condition rating must be between 1 and 5.',
            'vehicle_condition_rating.max' => 'Vehicle condition rating must be between 1 and 5.',
            'comment.min' => 'Comment must be at least 10 characters.',
            'comment.max' => 'Comment must not exceed 1000 characters.',
        ];
    }
}
