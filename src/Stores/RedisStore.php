<?php
namespace nexphant\Cache\Stores;

class RedisStore
{
    private $redis;
    private string $prefix;

    public function __construct($redis, string $prefix = 'cache:')
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('Redis extension not loaded');
        }
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    public function get(string $key): mixed
    {
        $value = $this->redis->get($this->prefix . $key);
        return $value === false ? null : unserialize($value);
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $serialized = serialize($value);
        if ($ttl > 0) {
            return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
        }
        return $this->redis->set($this->prefix . $key, $serialized);
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($this->prefix . $key) > 0;
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($this->prefix . $key) > 0;
    }

    public function clear(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');
        if (empty($keys)) {
            return true;
        }
        return $this->redis->del($keys) > 0;
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }
        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }
}
