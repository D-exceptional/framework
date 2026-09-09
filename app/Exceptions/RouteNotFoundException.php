<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class RouteNotFoundException extends Exception 
{
    public int $status;

    public function __construct(
        string $message = 'Route not found',
        int $status = 404
    ) {
        parent::__construct($message, $status);
        $this->status = $status;
    }
}
