<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    public const CACHE_KEY = 'school-management.settings';

    public static function get(string $key, $default = null)
    {
        return static::cached()[$key] ?? $default;
    }

    /** All settings, cached until one is saved. */
    public static function cached(): array
    {
        try {
            return \Illuminate\Support\Facades\Cache::rememberForever(self::CACHE_KEY, fn () => static::pluck('value', 'key')->all());
        } catch (\Throwable $e) {
            return []; // table not migrated yet
        }
    }

    /** All settings as a key => value array. */
    public static function allValues(): array
    {
        return static::pluck('value', 'key')->all();
    }

    public static function put(string $key, $value, string $type = 'text'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type]);
        \Illuminate\Support\Facades\Cache::forget(self::CACHE_KEY);
    }
}
