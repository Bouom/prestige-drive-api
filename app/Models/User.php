<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_type_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'google_id',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'avatar_url',
        'date_of_birth',
        'gender',
        'language',
        'timezone',
        'address',
        'postal_code',
        'city',
        'country',
        'latitude',
        'longitude',
        'stripe_customer_id',
        'stripe_account_id',
        'average_rating',
        'total_ratings',
        'is_active',
        'is_verified',
        'last_login_at',
        'last_login_ip',
        'metadata',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'average_rating' => 'decimal:2',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'last_login_at' => 'datetime',
        'metadata' => 'array',
        'password' => 'hashed',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user's type.
     */
    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    /**
     * Get the user's driver profile (if they are a driver).
     */
    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class, 'user_id');
    }

    /**
     * Get all rides where this user is the customer.
     */
    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class, 'customer_id');
    }

    /**
     * Get all reviews written by this user.
     */
    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Get all reviews received by this user (as a driver).
     */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    /**
     * Get all companies this user belongs to.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot(['role', 'permissions', 'is_active', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    /**
     * Get the company this user manages.
     */
    public function managedCompany(): HasOne
    {
        return $this->hasOne(Company::class, 'manager_id');
    }

    /**
     * Get all payment methods.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the default payment method.
     */
    public function defaultPaymentMethod(): HasOne
    {
        return $this->hasOne(PaymentMethod::class)->where('is_default', true);
    }

    /**
     * Get all payments made by this user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all news articles authored by this user.
     */
    public function newsArticles(): HasMany
    {
        return $this->hasMany(NewsArticle::class, 'author_id');
    }

    /**
     * Get all pages authored by this user.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'author_id');
    }

    /**
     * Get all audit logs for actions performed by this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get all notifications for this user.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Get media (polymorphic).
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->userType->is_admin;
    }

    /**
     * Check if user is a driver.
     */
    public function isDriver(): bool
    {
        return $this->userType->is_driver;
    }

    /**
     * Check if user is a company user.
     */
    public function isCompany(): bool
    {
        return $this->userType->name === 'company';
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->userType->hasPermission($permissionName);
    }

    /**
     * Get route key name (use UUID instead of ID).
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
