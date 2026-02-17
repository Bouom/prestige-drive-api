<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'module',
        'parent_id',
        'guard_name',
    ];

    /**
     * Get the parent permission.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }

    /**
     * Get all child permissions.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }

    /**
     * Get all user types (roles) that have this permission.
     */
    public function userTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            UserType::class,
            'role_permission',
            'permission_id',
            'user_type_id'
        )->withTimestamps();
    }

    /**
     * Scope to get permissions by module.
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }
}
