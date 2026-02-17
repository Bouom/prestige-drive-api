<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'trip_type_id',
        'applicable_countries',
        'base_rate_per_km',
        'minimum_fare',
        'base_fee',
        'distance_tiers',
        'time_multipliers',
        'surge_enabled',
        'max_surge_multiplier',
        'return_fee_base',
        'return_fee_per_km',
        'platform_commission_rate',
        'valid_from',
        'valid_until',
        'is_active',
        'description',
        'metadata',
    ];

    protected $casts = [
        'applicable_countries' => 'array',
        'base_rate_per_km' => 'decimal:2',
        'minimum_fare' => 'decimal:2',
        'base_fee' => 'decimal:2',
        'distance_tiers' => 'array',
        'time_multipliers' => 'array',
        'surge_enabled' => 'boolean',
        'max_surge_multiplier' => 'decimal:2',
        'return_fee_base' => 'decimal:2',
        'return_fee_per_km' => 'decimal:2',
        'platform_commission_rate' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the trip type.
     */
    public function tripType(): BelongsTo
    {
        return $this->belongsTo(TripType::class);
    }

    /**
     * Get all pricing snapshots using this rule.
     */
    public function pricingSnapshots(): HasMany
    {
        return $this->hasMany(PricingSnapshot::class);
    }

    /**
     * Scope to get active pricing rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            });
    }
}
