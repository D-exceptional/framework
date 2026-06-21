<?php

namespace App\Contracts;

interface CacheInterface
{
    public function get(string $key, mixed $default = null);

    public function set(string $key, mixed $value, int $ttl = 60): bool;

    public function delete(string $key): bool;
}