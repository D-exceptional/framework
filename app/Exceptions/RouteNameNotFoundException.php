<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class RouteNameNotFoundException extends Exception 
{
    public function __construct(
        string $message = 'Route name not found',
        public int $status = 404
    ) {
        parent::__construct($message, $this->status);
    }
}
