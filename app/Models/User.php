<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'google_id',
        'avatar',
        'company_name', 
        'company_address', 
        'manager_name', 
        'company_zip_code',
        'company_city', 
        'company_country', 
        'driver_count', 
        'company_iban', 
        'bic_code',
        'insurance_issue_date', 
        'insurance_expiry_date',
        'is_available', 
        'license_type', 
        'experience',
        'id_issue_date', 
        'id_expiry_date',
        'license_issue_date', 
        'license_expiry_date',
        'pro_card_issue_date', 
        'pro_card_expiry_date',
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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'insurance_issue_date' => 'date',
            'insurance_expiry_date' => 'date',
            'id_issue_date' => 'date',
            'id_expiry_date' => 'date',
            'license_issue_date' => 'date',
            'license_expiry_date' => 'date',
            'pro_card_issue_date' => 'date',
            'pro_card_expiry_date' => 'date',
            'is_available' => 'boolean',
        ];
    }
}
