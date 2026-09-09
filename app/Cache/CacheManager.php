<?php

declare(strict_types=1);

namespace App\Cache;

use App\Contracts\CacheInterface;

class CacheManager
{
    public function __construct(
        protected CacheInterface $driver
    ) {}

    public function driver(): CacheInterface
    {
        return $this->driver;
    }
}