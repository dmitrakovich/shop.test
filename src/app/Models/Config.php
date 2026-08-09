<?php

namespace App\Models;

use App\Enums\Config\ConfigKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ItemNotFoundException;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property ConfigKey $key
 * @property array<string, mixed> $config
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Config extends Model implements Auditable
{
    use AuditableTrait;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['key', 'config'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'key' => ConfigKey::class,
        'config' => 'array',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (self $config) {
            Cache::forget('config.' . $config->key->value);
        });
    }

    public static function findByKey(ConfigKey $key): ?self
    {
        return self::query()->where('key', $key)->first();
    }

    public static function findByKeyOrFail(ConfigKey $key): self
    {
        return self::query()->where('key', $key)->firstOrFail();
    }

    /**
     * Find a cached config by its business key or throw an exception.
     *
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public static function findCacheable(ConfigKey $key): array
    {
        return Cache::rememberForever(
            'config.' . $key->value,
            fn () => self::findOrException($key)->config
        );
    }

    /**
     * Read a nested value from a cached config (dot notation), with an optional default.
     */
    public static function value(ConfigKey $key, string $path, mixed $default = null): mixed
    {
        return data_get(self::findCacheable($key), $path, $default);
    }

    /**
     * @throws \Exception
     */
    private static function findOrException(ConfigKey $key): self
    {
        $config = self::findByKey($key);

        if ($config === null) {
            throw new ItemNotFoundException("Config with key '{$key->value}' not found");
        }

        return $config;
    }
}
