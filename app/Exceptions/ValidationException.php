<?php
namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public array $errors;
    public int $status;

    public function __construct(
        array $errors, 
        string $message = 'Validation failed', 
        int $status = 422
    )
    {
        parent::__construct($message);
        $this->errors = $errors;
        $this->status = $status;
    }
}
