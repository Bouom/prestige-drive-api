<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'base_price_multiplier',
        'minimum_fare',
        'required_vehicle_class',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'base_price_multiplier' => 'decimal:2',
        'minimum_fare' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all rides of this type.
     */
    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }

    /**
     * Get all pricing rules for this trip type.
     */
    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    /**
     * Get all quotes for this trip type.
     */
    public function rideQuotes(): HasMany
    {
        return $this->hasMany(RideQuote::class);
    }

    /**
     * Scope to get only active trip types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
