<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated and be a company admin
        $company = $this->route('company');

        return $this->user() && (
            $this->user()->companies->contains($company) ||
            $this->user()->isAdmin()
        );
    }

    public function rules(): array
    {
        return [
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpeg,png,jpg',
                'max:10240', // 10MB max for company documents
            ],
            'document_number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'issued_at' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type_id.required' => 'Please select the document type.',
            'document_type_id.exists' => 'The selected document type is invalid.',
            'file.required' => 'Please upload a document file.',
            'file.file' => 'Invalid file upload.',
            'file.mimes' => 'Document must be a PDF, JPEG, PNG, or JPG file.',
            'file.max' => 'File size must not exceed 10MB.',
            'issued_at.before_or_equal' => 'Issue date cannot be in the future.',
            'expires_at.after' => 'Document must not be expired.',
        ];
    }
}
