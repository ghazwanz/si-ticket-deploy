<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key, with dynamic caching and fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("system_setting.{$key}", 60, function () use ($key, $default) {
            $setting = self::find($key);

            return $setting !== null ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key, and clear cache.
     */
    public static function set(string $key, mixed $value): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );

        Cache::forget("system_setting.{$key}");

        return $setting;
    }
}
