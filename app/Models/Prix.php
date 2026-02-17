<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Prix extends Model
{
    use HasFactory;

    protected $table = 'prix';

    protected $fillable = [
        'montant',
        'is_active',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get the active prix.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the currently active per-km rate.
     * Falls back to config value if none is active.
     */
    public static function getActiveRate(): float
    {
        $active = static::where('is_active', true)->first();

        return $active ? (float) $active->montant : (float) config('lcp.ride.base_price_per_km', 2.50);
    }

    /**
     * Activate this prix and deactivate all others.
     */
    public function activate(): void
    {
        DB::transaction(function () {
            static::where('is_active', true)->update(['is_active' => false]);
            $this->update(['is_active' => true]);
        });
    }
}
