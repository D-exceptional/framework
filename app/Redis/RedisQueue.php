<?php

namespace App\Redis;

use Predis\Client;

abstract class RedisQueue 
{
    public function __construct(
        protected Client $redis
    ) {
        $this->redis = $redis;
    }

    public function push(
        string $queue,
        mixed $payload
    ): bool {

        return (bool) $this->redis->rpush(
            "queue:{$queue}",
            $payload
        );
    }

    public function pop(
        string $queue
    ): mixed {

        return $this->redis->blpop(
            "queue:{$queue}",
            2
        );
    }
}