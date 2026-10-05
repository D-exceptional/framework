<?php

namespace App\Exceptions;

use LogicException;

class InvalidRoutePatternException extends LogicException
{
    public function __construct(
        string $message = 'Invalid route pattern',
        public int $status = 500
    ) {
        parent::__construct($message, $this->status);
    }
}