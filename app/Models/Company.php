<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'legal_name',
        'trade_name',
        'registration_number',
        'vat_number',
        'email',
        'phone',
        'website',
        'address',
        'postal_code',
        'city',
        'country',
        'representative_name',
        'representative_position',
        'iban',
        'bic',
        'billing_email',
        'total_drivers',
        'active_drivers',
        'total_vehicles',
        'is_active',
        'is_verified',
        'verified_at',
        'verified_by',
        'stripe_account_id',
        'logo_url',
        'description',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            if (empty($company->uuid)) {
                $company->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the verifier (admin user).
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get all driver profiles belonging to this company.
     */
    public function driverProfiles(): HasMany
    {
        return $this->hasMany(DriverProfile::class);
    }

    /**
     * Get all vehicles owned by this company.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get all rides managed by this company.
     */
    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }

    /**
     * Get all users (admins, dispatchers) belonging to this company.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot(['role', 'permissions', 'is_active', 'joined_at', 'left_at'])
            ->withTimestamps();
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
     * Check if a given user is an owner/member of this company.
     */
    public function isOwner(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Scope to get only verified companies.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get only active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
