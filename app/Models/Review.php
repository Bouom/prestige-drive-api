<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ride_id',
        'reviewer_id',
        'reviewee_id',
        'overall_rating',
        'cleanliness_rating',
        'punctuality_rating',
        'driving_quality_rating',
        'professionalism_rating',
        'vehicle_condition_rating',
        'comment',
        'driver_response',
        'driver_responded_at',
        'is_published',
        'is_flagged',
        'flagged_reason',
        'moderated_by',
        'moderated_at',
        'moderation_action',
    ];

    protected $casts = [
        'overall_rating' => 'decimal:2',
        'driver_responded_at' => 'datetime',
        'is_published' => 'boolean',
        'is_flagged' => 'boolean',
        'moderated_at' => 'datetime',
    ];

    /**
     * Get the ride being reviewed.
     */
    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    /**
     * Get the reviewer (customer).
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the reviewee (driver).
     */
    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    /**
     * Get the moderator.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Scope to get published reviews.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('is_flagged', false);
    }

    /**
     * Scope to get flagged reviews.
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }
}
