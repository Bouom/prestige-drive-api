<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingSnapshot extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'ride_id',
        'pricing_rule_id',
        'pricing_rule_version',
        'distance_km',
        'base_rate_per_km',
        'base_calculation',
        'surcharges',
        'discounts',
        'return_fee',
        'platform_fee_rate',
        'platform_fee_amount',
        'subtotal',
        'total_surcharges',
        'total_discounts',
        'tax_rate',
        'tax_amount',
        'final_total',
        'calculated_at',
        'calculation_metadata',
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'base_rate_per_km' => 'decimal:2',
        'base_calculation' => 'array',
        'surcharges' => 'array',
        'discounts' => 'array',
        'return_fee' => 'decimal:2',
        'platform_fee_rate' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_surcharges' => 'decimal:2',
        'total_discounts' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_total' => 'decimal:2',
        'calculated_at' => 'datetime',
        'calculation_metadata' => 'array',
    ];

    /**
     * Get the ride.
     */
    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    /**
     * Get the pricing rule.
     */
    public function pricingRule(): BelongsTo
    {
        return $this->belongsTo(PricingRule::class);
    }
}
