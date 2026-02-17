<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ride_id',
        'driver_id',
        'vehicle_id',
        'violation_type',
        'violation_date',
        'location',
        'fine_amount',
        'currency',
        'responsible_party',
        'ticket_number',
        'ticket_document_url',
        'is_paid',
        'paid_at',
        'paid_by',
        'payment_method',
        'is_disputed',
        'dispute_reason',
        'dispute_status',
        'dispute_resolved_at',
        'admin_notes',
        'metadata',
    ];

    protected $casts = [
        'violation_date' => 'datetime',
        'fine_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'is_disputed' => 'boolean',
        'dispute_resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the related ride.
     */
    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    /**
     * Get the driver.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class, 'driver_id');
    }

    /**
     * Get the vehicle.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who paid the fine.
     */
    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Scope to get unpaid violations.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    /**
     * Scope to get disputed violations.
     */
    public function scopeDisputed($query)
    {
        return $query->where('is_disputed', true)
            ->where('dispute_status', 'pending');
    }
}
