<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class MethodNotAllowedException extends Exception 
{
    public function __construct(
        string $message = 'Method not allowed',
        public int $status = 405
    ) {
        parent::__construct($message, $this->status);
    }
}