<?php
namespace App\Exceptions;

class MiddlewareException extends \Exception 
{
    public int $status;
    public string $action;
    public ?string $redirect;

    public function __construct(
        string $message = 'Blocked by middleware',
        int $status = 403,
        string $action = 'json',
        ?string $redirect = null
    ) {
        parent::__construct($message, $status);
        $this->status   = $status;
        $this->action   = $action;
        $this->redirect = $redirect;
    }
}
