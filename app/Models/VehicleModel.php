<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_brand_id',
        'name',
        'slug',
        'vehicle_class',
        'body_type',
        'typical_passenger_capacity',
        'typical_luggage_capacity',
        'image_url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the brand this model belongs to.
     */
    public function vehicleBrand(): BelongsTo
    {
        return $this->belongsTo(VehicleBrand::class);
    }

    /**
     * Get all vehicles of this model.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Scope to get only active models.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get models by class.
     */
    public function scopeByClass($query, $class)
    {
        return $query->where('vehicle_class', $class);
    }

    /**
     * Get full model name (brand + model).
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->vehicleBrand->name} {$this->name}";
    }
}
