<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'current_driver_id',
        'vehicle_model_id',
        'license_plate',
        'vin',
        'registration_number',
        'year',
        'color',
        'fuel_type',
        'transmission',
        'passenger_capacity',
        'luggage_capacity',
        'features',
        'vehicle_class',
        'is_active',
        'is_available',
        'current_status',
        'last_maintenance_at',
        'next_maintenance_at',
        'total_km',
        'insurance_company',
        'insurance_policy_number',
        'insurance_expires_at',
        'photos',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'last_maintenance_at' => 'date',
        'next_maintenance_at' => 'date',
        'insurance_expires_at' => 'date',
        'features' => 'array',
        'photos' => 'array',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vehicle) {
            if (empty($vehicle->uuid)) {
                $vehicle->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the company that owns this vehicle.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the currently assigned driver.
     */
    public function currentDriver(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class, 'current_driver_id');
    }

    /**
     * Get the vehicle model.
     */
    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    /**
     * Get all rides using this vehicle.
     */
    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }

    /**
     * Get all violations for this vehicle.
     */
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    /**
     * Get documents (polymorphic).
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get media (polymorphic).
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get rating (polymorphic).
     */
    public function rating(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Get route key name (use UUID instead of ID).
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Scope to get only available vehicles.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where('current_status', 'available');
    }

    /**
     * Scope to get vehicles by class.
     */
    public function scopeByClass($query, $class)
    {
        return $query->where('vehicle_class', $class);
    }
}
