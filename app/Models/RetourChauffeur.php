<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RetourChauffeur extends Model
{
    use HasFactory;

    protected $table = 'retour_chauffeur';

    protected $fillable = [
        'montant',
        'is_active',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get the active retour chauffeur fee.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the currently active flat return fee.
     * Falls back to config value if none is active.
     */
    public static function getActiveFee(): float
    {
        $active = static::where('is_active', true)->first();

        return $active ? (float) $active->montant : (float) config('lcp.ride.return_fee_base', 20.00);
    }

    /**
     * Activate this retour chauffeur fee and deactivate all others.
     */
    public function activate(): void
    {
        DB::transaction(function () {
            static::where('is_active', true)->update(['is_active' => false]);
            $this->update(['is_active' => true]);
        });
    }
}
