<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'target_type',
        'starts_at',
        'max_uses',
        'used_count',
        'is_active',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValid(): bool
    {
        return $this->is_active
            && ($this->starts_at === null || $this->starts_at->isPast() || $this->starts_at->isCurrentMinute())
            && ($this->expires_at === null || $this->expires_at->isFuture())
            && ($this->max_uses === null || $this->used_count < $this->max_uses);
    }

    public function isApplicableTo(?User $user = null, ?string $customerType = null): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        $target = $this->target_type;
        if ($target === 'corporate') {
            $target = 'company';
        }

        if ($target === 'all') {
            return true;
        }

        if ($customerType) {
            $normalizedCustomerType = $customerType === 'corporate' ? 'company' : $customerType;
            return $normalizedCustomerType === $target;
        }

        if (! $user) {
            return false;
        }

        $userType = $user->userType->name ?? null;

        if ($target === 'company') {
            return $userType === 'company';
        }

        if ($target === 'individual') {
            return $userType === 'client';
        }

        return false;
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
