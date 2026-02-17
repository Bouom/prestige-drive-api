<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'applies_to',
        'is_required',
        'requires_expiry_date',
        'requires_document_number',
        'allowed_file_types',
        'max_file_size_mb',
        'requires_admin_approval',
        'expiry_alert_days_before',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'applies_to' => 'array',
        'is_required' => 'boolean',
        'requires_expiry_date' => 'boolean',
        'requires_document_number' => 'boolean',
        'allowed_file_types' => 'array',
        'requires_admin_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all documents of this type.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Scope to get only active document types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if this document type applies to a given model.
     */
    public function appliesTo(string $modelType): bool
    {
        return in_array($modelType, $this->applies_to ?? []);
    }
}
