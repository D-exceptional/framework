<?php

namespace App\Session;

use App\Contracts\SessionInterface;

class SessionManager
{
    protected SessionInterface $driver;

    public function __construct(SessionInterface $driver)
    {
        $this->driver = $driver;
    }

    // =========================================
    // GET SESSION DRIVER
    // =========================================
    public function driver(): SessionInterface
    {
        return $this->driver;
    }
}