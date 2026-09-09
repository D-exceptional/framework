<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class MiddlewareException extends Exception 
{
    public int $status;

    public function __construct(
        string $message = 'Blocked by middleware',
        int $status = 403,
        protected array $headers = []
    ) {
        parent::__construct($message, $status);
        $this->status   = $status;
        $this->headers  = $headers;
    }
}
