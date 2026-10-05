<?php

namespace App\Exceptions;

use LogicException;

class DuplicateRouteNameException extends LogicException
{
    public function __construct(
        string $message = 'Duplicate route name detected',
        public int $status = 500
    ) {
        parent::__construct($message, $this->status);
    }
}