<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public function __construct( 
        string $message = 'Validation failed', 
        public int $status = 422,
        public array $errors = []
    ) {
        parent::__construct($message, $this->status);
        $this->errors = $errors;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
