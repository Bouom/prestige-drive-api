<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'vehicle_id',
        'license_type_id',
        'license_number',
        'license_issued_at',
        'license_expires_at',
        'professional_card_number',
        'years_experience',
        'employment_type',
        'joined_platform_at',
        'is_available',
        'is_on_ride',
        'accepts_shared_rides',
        'max_passengers',
        'current_latitude',
        'current_longitude',
        'location_updated_at',
        'heading',
        'total_rides',
        'completed_rides',
        'cancelled_rides',
        'acceptance_rate',
        'cancellation_rate',
        'average_response_time',
        'total_earnings',
        'pending_payout',
        'is_verified',
        'verified_at',
        'verified_by',
        'iban',
        'bic',
        'bank_account_holder',
        'bio',
        'languages',
        'metadata',
    ];

    protected $casts = [
        'license_issued_at' => 'date',
        'license_expires_at' => 'date',
        'joined_platform_at' => 'date',
        'is_available' => 'boolean',
        'is_on_ride' => 'boolean',
        'accepts_shared_rides' => 'boolean',
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'location_updated_at' => 'datetime',
        'acceptance_rate' => 'decimal:2',
        'cancellation_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'pending_payout' => 'decimal:2',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'languages' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the user associated with this driver profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company this driver belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the currently assigned vehicle.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the license type.
     */
    public function licenseType(): BelongsTo
    {
        return $this->belongsTo(LicenseType::class);
    }

    /**
     * Get the verifier (admin user).
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get all rides assigned to this driver.
     */
    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class, 'driver_id');
    }

    /**
     * Get all payouts for this driver.
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(DriverPayout::class);
    }

    /**
     * Get all violations.
     */
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'driver_id');
    }

    /**
     * Get documents (polymorphic).
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get rating (polymorphic).
     */
    public function rating(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Scope to get only available drivers.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where('is_on_ride', false)
            ->where('is_verified', true);
    }

    /**
     * Scope to get drivers near a location.
     */
    public function scopeNearby($query, $latitude, $longitude, $radiusKm = 10)
    {
        return $query->selectRaw('*, (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(current_latitude)) * COS(RADIANS(current_longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(current_latitude)))) AS distance', [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }
}
