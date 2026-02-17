<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'documentable_type',
        'documentable_id',
        'document_type_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'file_hash',
        'document_number',
        'issued_at',
        'expires_at',
        'status',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'expiry_notification_sent_at',
        'version',
        'replaces_document_id',
        'metadata',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
        'verified_at' => 'datetime',
        'expiry_notification_sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the documentable model (polymorphic).
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the document type.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Get the verifier (admin user).
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the document this replaces.
     */
    public function replacesDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'replaces_document_id');
    }

    /**
     * Get route key name (use UUID instead of ID).
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Scope to get pending documents.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved documents.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get expiring documents.
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'approved')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }
}
