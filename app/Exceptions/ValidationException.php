<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public int $status;
    public array $errors;

    public function __construct( 
        string $message = 'Validation failed', 
        int $status = 422,
        array $errors = []
    ) {
        parent::__construct($message);
        $this->status = $status;
        $this->errors = $errors;
    }
}
