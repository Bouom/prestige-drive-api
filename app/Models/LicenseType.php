<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicenseType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'minimum_age',
        'requires_professional_card',
        'max_passenger_capacity',
        'max_vehicle_weight',
        'is_active',
    ];

    protected $casts = [
        'requires_professional_card' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all driver profiles with this license type.
     */
    public function driverProfiles(): HasMany
    {
        return $this->hasMany(DriverProfile::class);
    }

    /**
     * Scope to get only active license types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
