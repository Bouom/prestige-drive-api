<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RideWaypoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'ride_id',
        'sequence',
        'address',
        'latitude',
        'longitude',
        'waypoint_type',
        'estimated_arrival',
        'actual_arrival',
        'wait_time_minutes',
        'is_completed',
        'completed_at',
        'notes',
        'contact_name',
        'contact_phone',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'estimated_arrival' => 'datetime',
        'actual_arrival' => 'datetime',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the ride this waypoint belongs to.
     */
    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }
}
