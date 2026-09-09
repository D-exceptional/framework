<?php

declare(strict_types=1);

namespace App\Redis;

use Predis\Client;

abstract class RedisStore
{
    public function __construct(
        protected Client $redis
    ) {}

    public function getValue(
        string $key
    ): mixed {

        $value = $this->redis->get($key);

        return $value ?? null;
    }

    public function setValue(
        string $key,
        mixed $value,
        int $ttl = 0
    ): bool {

        if ($ttl > 0) {

            return (bool) $this->redis->setex(
                $key,
                $ttl,
                $value
            );
        }

        return (bool) $this->redis->set(
            $key,
            $value
        );
    }

    public function deleteValue(
        string $key
    ): int {

        return $this->redis->del([$key]);
    }

    public function keyExists(
        string $key
    ): bool {

        return (bool) $this->redis->exists($key);
    }

    public function getTtl(
        string $key
    ): int {

        return $this->redis->ttl($key);
    }

    public function setExpire(
        string $key,
        int $seconds
    ): bool {

        return (bool) $this->redis->expire(
            $key,
            $seconds
        );
    }

    public function incrementValue(
        string $key,
        int $by = 1
    ): int {

        return $this->redis->incrby(
            $key,
            $by
        );
    }

    public function decrementValue(
        string $key,
        int $by = 1
    ): int {

        return $this->redis->decrby(
            $key,
            $by
        );
    }

    public function rememberValue(
        string $key,
        int $ttl,
        callable $callback
    ): mixed {

        if ($this->keyExists($key)) {
            return $this->getValue($key);
        }

        $value = $callback();

        $this->setValue(
            $key,
            $value,
            $ttl
        );

        return $value;
    }

    public function flushDb(): bool
    {
        return (bool) $this->redis->flushdb();
    }

    public function getKeys(
        string $pattern = '*'
    ): array {

        return $this->redis->keys(
            $pattern
        );
    }
}