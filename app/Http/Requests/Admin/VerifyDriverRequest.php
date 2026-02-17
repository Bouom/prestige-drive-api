<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VerifyDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins with verification permission can verify drivers
        return $this->user() && $this->user()->can('verify-drivers');
    }

    public function rules(): array
    {
        return [
            'driver_profile_id' => ['required', 'integer', 'exists:driver_profiles,id'],
            'is_verified' => ['required', 'boolean'],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],

            // If rejecting, reason is required
            'rejection_reason' => ['required_if:is_verified,false', 'nullable', 'string', 'max:1000'],

            // Document verification statuses (optional, to verify specific documents)
            'documents' => ['sometimes', 'array'],
            'documents.*.document_id' => ['required', 'integer', 'exists:documents,id'],
            'documents.*.status' => ['required', 'in:approved,rejected'],
            'documents.*.rejection_reason' => ['required_if:documents.*.status,rejected', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'driver_profile_id.required' => 'Driver profile ID is required.',
            'driver_profile_id.exists' => 'The specified driver profile does not exist.',
            'is_verified.required' => 'Verification status is required.',
            'rejection_reason.required_if' => 'Rejection reason is required when rejecting a driver.',
            'documents.*.document_id.required' => 'Document ID is required.',
            'documents.*.document_id.exists' => 'One or more documents do not exist.',
            'documents.*.status.required' => 'Document status is required.',
            'documents.*.status.in' => 'Document status must be approved or rejected.',
            'documents.*.rejection_reason.required_if' => 'Rejection reason is required for rejected documents.',
        ];
    }
}
