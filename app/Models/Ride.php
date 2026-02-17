<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ride extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'booking_reference',
        'customer_id',
        'driver_id',
        'company_id',
        'trip_type_id',
        'vehicle_id',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'dropoff_address',
        'dropoff_latitude',
        'dropoff_longitude',
        'scheduled_at',
        'accepted_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'estimated_distance_km',
        'estimated_duration_min',
        'actual_distance_km',
        'actual_duration_min',
        'passenger_count',
        'has_luggage',
        'requires_child_seat',
        'is_round_trip',
        'return_scheduled_at',
        'base_price',
        'return_fee',
        'surcharge',
        'discount_amount',
        'discount_code',
        'total_price',
        'platform_fee',
        'taxes',
        'final_price',
        'driver_earnings',
        'status',
        'payment_status',
        'customer_notes',
        'driver_notes',
        'admin_notes',
        'cancellation_reason',
        'requires_date_confirmation',
        'date_confirmed_at',
        'pickup_photo_url',
        'dropoff_photo_url',
        'metadata',
    ];

    protected $casts = [
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'dropoff_latitude' => 'decimal:8',
        'dropoff_longitude' => 'decimal:8',
        'scheduled_at' => 'datetime',
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'return_scheduled_at' => 'datetime',
        'estimated_distance_km' => 'decimal:2',
        'actual_distance_km' => 'decimal:2',
        'has_luggage' => 'boolean',
        'requires_child_seat' => 'boolean',
        'is_round_trip' => 'boolean',
        'base_price' => 'decimal:2',
        'return_fee' => 'decimal:2',
        'surcharge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'taxes' => 'decimal:2',
        'final_price' => 'decimal:2',
        'driver_earnings' => 'decimal:2',
        'requires_date_confirmation' => 'boolean',
        'date_confirmed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ride) {
            if (empty($ride->uuid)) {
                $ride->uuid = (string) Str::uuid();
            }
            if (empty($ride->booking_reference)) {
                $ride->booking_reference = 'LCP-'.date('Y').'-'.str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the driver.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class, 'driver_id');
    }

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the trip type.
     */
    public function tripType(): BelongsTo
    {
        return $this->belongsTo(TripType::class);
    }

    /**
     * Get the vehicle.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get all waypoints.
     */
    public function waypoints(): HasMany
    {
        return $this->hasMany(RideWaypoint::class)->orderBy('sequence');
    }

    /**
     * Get status history.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(RideStatusHistory::class);
    }

    /**
     * Get pricing snapshot.
     */
    public function pricingSnapshot(): HasOne
    {
        return $this->hasOne(PricingSnapshot::class);
    }

    /**
     * Get review.
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Get payments (polymorphic).
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Get media (polymorphic).
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get route key name (use UUID instead of ID).
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Scope to get upcoming rides.
     */
    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['confirmed', 'assigned', 'accepted'])
            ->where('scheduled_at', '>', now());
    }

    /**
     * Scope to get completed rides.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
