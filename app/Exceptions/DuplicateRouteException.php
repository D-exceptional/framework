<?php

namespace App\Exceptions;

use LogicException;

class DuplicateRouteException extends LogicException
{
    public function __construct(
        string $message = 'Duplicate route detected',
        public int $status = 500
    ) {
        parent::__construct($message, $this->status);
    }
}