<?php

declare(strict_types=1);

namespace App\Session;

use App\Contracts\SessionInterface;

class SessionManager
{
    public function __construct(
        protected SessionInterface $driver
    ) {}

    public function driver(): SessionInterface
    {
        return $this->driver;
    }
}