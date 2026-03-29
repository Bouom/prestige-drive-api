<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RideQuote extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'dropoff_address',
        'dropoff_latitude',
        'dropoff_longitude',
        'trip_type_id',
        'trip_purpose',
        'is_round_trip',
        'passenger_count',
        'vehicle_brand_id',
        'vehicle_model_id',
        'scheduled_at',
        'return_scheduled_at',
        'estimated_distance_km',
        'estimated_duration_min',
        'estimated_price',
        'converted_to_ride_id',
        'session_id',
        'guest_token',
        'ip_address',
        'expires_at',
    ];

    protected $casts = [
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'dropoff_latitude' => 'decimal:8',
        'dropoff_longitude' => 'decimal:8',
        'is_round_trip' => 'boolean',
        'estimated_distance_km' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the trip type.
     */
    public function tripType(): BelongsTo
    {
        return $this->belongsTo(TripType::class);
    }

    public function vehicleBrand(): BelongsTo
    {
        return $this->belongsTo(VehicleBrand::class);
    }

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    /**
     * Get the converted ride.
     */
    public function convertedToRide(): BelongsTo
    {
        return $this->belongsTo(Ride::class, 'converted_to_ride_id');
    }

    /**
     * Scope to get unexpired quotes.
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope to get converted quotes.
     */
    public function scopeConverted($query)
    {
        return $query->whereNotNull('converted_to_ride_id');
    }

    /**
     * Scope to get quotes by guest token.
     */
    public function scopeForGuest($query, string $guestToken)
    {
        return $query->where('guest_token', $guestToken);
    }
}
