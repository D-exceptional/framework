<?php

declare(strict_types=1);

namespace App\Cache\Drivers;

use App\Redis\RedisManager;
use App\Redis\RedisStore;
use App\Contracts\CacheInterface;

class RedisDriver extends RedisStore implements CacheInterface 
{
    public function __construct(
        RedisManager $redis
    ) {
        parent::__construct(
            $redis->cache()
        );
    }

    // =========================================
    // GET REDIS CACHE DATA
    // =========================================
    public function get(
        string $key,
        mixed $default = null
    ): mixed {

        $value = $this->getValue($key);

        if ($value === null) {
            return $default;
        }

        return json_decode($value, true);
    }

    // =========================================
    // SET REDIS CACHE DATA
    // =========================================
    public function set(
        string $key,
        mixed $value,
        int $ttl = 60
    ): bool {

        return (bool) $this->setValue(
            $key,
            $ttl,
            json_encode($value)
        );
    }

    // =========================================
    // DELETE REDIS CACHE DATA
    // =========================================
    public function delete(
        string $key
    ): bool {

        return (bool) $this->deleteValue($key);
    }

    // =========================================
    // REMEMBER REDIS CACHE DATA
    // =========================================
    public function remember(
        string $key,
        callable $callback,
        int $ttl = 60
    ): mixed {

        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();

        $this->set($key, $value, $ttl);

        return $value;
    }
}