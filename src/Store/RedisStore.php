<?php

namespace Nexphant\Cache\Store;

class RedisStore
{
    private $redis;
    private string $prefix;

    public function __construct(string $host = '127.0.0.1', int $port = 6379, string $prefix = 'nexphant:')
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('ext-redis not available');
        }
        $this->redis = new \Redis();
        $this->redis->connect($host, $port);
        $this->prefix = $prefix;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($this->prefix . $key);
        return $value === false ? $default : unserialize($value);
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $serialized = serialize($value);
        if ($ttl > 0) {
            return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
        }
        return $this->redis->set($this->prefix . $key, $serialized);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($this->prefix . $key) > 0;
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($this->prefix . $key) > 0;
    }

    public function increment(string $key, int $step = 1): int|false
    {
        return $this->redis->incrBy($this->prefix . $key, $step);
    }

    public function decrement(string $key, int $step = 1): int|false
    {
        return $this->redis->decrBy($this->prefix . $key, $step);
    }

    public function clear(): bool
    {
        return $this->redis->flushDB();
    }
}
