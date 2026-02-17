<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group_name',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the value with proper type casting.
     *
     * @return mixed
     */
    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            'array' => json_decode($this->value, true) ?? [],
            default => $this->value,
        };
    }

    /**
     * Set the value with proper type casting.
     *
     * @param  mixed  $value
     */
    public function setTypedValue($value): void
    {
        $this->value = match ($this->type) {
            'integer' => (string) (int) $value,
            'float' => (string) (float) $value,
            'boolean' => $value ? '1' : '0',
            'json', 'array' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get a setting value by key.
     *
     * @param  mixed  $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->typed_value : $default;
    }

    /**
     * Set a setting value by key.
     *
     * @param  mixed  $value
     */
    public static function set(string $key, $value, string $type = 'string'): static
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->type = $type;
        $setting->setTypedValue($value);
        $setting->save();

        return $setting;
    }

    /**
     * Get all settings as key-value array.
     */
    public static function getAll(bool $publicOnly = false): array
    {
        $query = static::query();

        if ($publicOnly) {
            $query->where('is_public', true);
        }

        return $query->get()->pluck('typed_value', 'key')->toArray();
    }

    /**
     * Get settings by group.
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group_name', $group)
            ->get()
            ->pluck('typed_value', 'key')
            ->toArray();
    }
}
