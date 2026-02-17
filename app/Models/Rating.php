<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    use HasFactory;

    const CREATED_AT = null;

    protected $fillable = [
        'rateable_type',
        'rateable_id',
        'average_rating',
        'total_ratings',
        'rating_distribution',
        'average_cleanliness',
        'average_punctuality',
        'average_driving_quality',
        'average_professionalism',
        'average_vehicle_condition',
        'last_calculated_at',
    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
        'rating_distribution' => 'array',
        'average_cleanliness' => 'decimal:2',
        'average_punctuality' => 'decimal:2',
        'average_driving_quality' => 'decimal:2',
        'average_professionalism' => 'decimal:2',
        'average_vehicle_condition' => 'decimal:2',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * Get the rateable model (polymorphic).
     */
    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Recalculate ratings from reviews.
     */
    public function recalculate()
    {
        // This would be implemented in a service class
        // to recalculate from the reviews table
    }
}
