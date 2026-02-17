<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_admin',
        'is_driver',
        'default_dashboard',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_driver' => 'boolean',
    ];

    /**
     * Get all users of this type.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_type_id');
    }

    /**
     * Get all permissions for this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permission',
            'user_type_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Check if this user type has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()
            ->where('name', $permissionName)
            ->exists();
    }
}
