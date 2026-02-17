<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'subtitle',
        'image_url',
        'mobile_image_url',
        'cta_text',
        'cta_url',
        'opens_in_new_tab',
        'placement',
        'starts_at',
        'ends_at',
        'sort_order',
        'is_active',
        'click_count',
        'impression_count',
    ];

    protected $casts = [
        'opens_in_new_tab' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get active banners.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope to get banners by placement.
     */
    public function scopeByPlacement($query, $placement)
    {
        return $query->where('placement', $placement);
    }
}
