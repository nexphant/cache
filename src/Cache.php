<?php
namespace Nexphant\Cache;

use Nexphant\Cache\Stores\ApcuStore;

class Cache
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return ApcuStore::get($key, $default);
    }

    public static function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return ApcuStore::set($key, $value, $ttl);
    }

    public static function has(string $key): bool
    {
        return ApcuStore::get($key) !== null;
    }

    public static function delete(string $key): bool
    {
        return ApcuStore::delete($key);
    }

    public static function clear(): bool
    {
        return ApcuStore::clear();
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = self::get($key);
        if ($value !== null) {
            return $value;
        }
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }
}
