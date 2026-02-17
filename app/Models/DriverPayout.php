<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_profile_id',
        'period_start',
        'period_end',
        'ride_count',
        'ride_ids',
        'gross_earnings',
        'platform_commission',
        'bonuses',
        'deductions',
        'net_payout',
        'payment_method',
        'payment_reference',
        'status',
        'calculated_at',
        'paid_at',
        'processed_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'ride_ids' => 'array',
        'gross_earnings' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_payout' => 'decimal:2',
        'calculated_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the driver profile.
     */
    public function driverProfile(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class);
    }

    /**
     * Get the user who processed the payout.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope to get pending payouts.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get paid payouts.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
