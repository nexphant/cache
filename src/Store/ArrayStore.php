<?php

namespace Nexphant\Cache\Store;

class ArrayStore
{
    private array $cache = [];
    private array $expires = [];
    private int $maxSize;
    private int $setCount = 0;

    public function __construct(int $maxSize = 10000)
    {
        $this->maxSize = max(1, $maxSize);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!isset($this->cache[$key])) {
            return $default;
        }

        if (isset($this->expires[$key]) && $this->expires[$key] < time()) {
            unset($this->cache[$key], $this->expires[$key]);
            return $default;
        }

        return $this->cache[$key];
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        if (!isset($this->cache[$key]) && count($this->cache) >= $this->maxSize) {
            $this->evictExpired();
            if (count($this->cache) >= $this->maxSize) {
                reset($this->cache);
                $oldest = key($this->cache);
                unset($this->cache[$oldest], $this->expires[$oldest]);
            }
        }

        $this->cache[$key] = $value;

        if ($ttl > 0) {
            $this->expires[$key] = time() + $ttl;
        } else {
            unset($this->expires[$key]);
        }

        if (++$this->setCount % 1000 === 0) {
            $this->evictExpired();
        }

        return true;
    }

    private function evictExpired(): void
    {
        $now = time();
        foreach ($this->expires as $key => $exp) {
            if ($exp < $now) {
                unset($this->cache[$key], $this->expires[$key]);
            }
        }
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key], $this->expires[$key]);
        return true;
    }

    public function has(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        if (isset($this->expires[$key]) && $this->expires[$key] < time()) {
            unset($this->cache[$key], $this->expires[$key]);
            return false;
        }

        return true;
    }

    public function increment(string $key, int $step = 1): int|false
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = 0;
        }

        if (!is_int($this->cache[$key])) {
            return false;
        }

        $this->cache[$key] += $step;
        return $this->cache[$key];
    }

    public function decrement(string $key, int $step = 1): int|false
    {
        return $this->increment($key, -$step);
    }

    public function clear(): bool
    {
        $this->cache = [];
        $this->expires = [];
        return true;
    }
}
