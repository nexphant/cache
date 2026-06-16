<?php

namespace Nexphant\Cache\Store;

class ApcuStore
{
    private string $prefix;

    public function __construct(string $prefix = 'nexphant:')
    {
        if (!extension_loaded('apcu')) {
            throw new \RuntimeException('ext-apcu is not available');
        }
        $this->prefix = $prefix;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = apcu_fetch($this->prefix . $key, $success);
        return $success ? $value : $default;
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        return apcu_store($this->prefix . $key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return apcu_delete($this->prefix . $key);
    }

    public function has(string $key): bool
    {
        return apcu_exists($this->prefix . $key);
    }

    public function increment(string $key, int $step = 1): int|false
    {
        return apcu_inc($this->prefix . $key, $step);
    }

    public function decrement(string $key, int $step = 1): int|false
    {
        return apcu_dec($this->prefix . $key, $step);
    }

    public function clear(): bool
    {
        return apcu_clear_cache();
    }
}
