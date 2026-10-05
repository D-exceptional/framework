<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class MiddlewareException extends Exception 
{    
    public function __construct(
        string $message = 'Request blocked by middleware',
        public int $status = 403,
        public ?string $action = null, 
        public ?string $redirect = null,
        protected array $headers = []
    ) {
        parent::__construct($message, $this->status);
    }

    public function headers(): array
    {
        return $this->headers;
    }
}
