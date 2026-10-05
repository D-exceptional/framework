<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Contracts\SessionInterface;

class GuestMiddleware
{
    public function __construct(
        protected SessionInterface $session
    ) {}

    public function handle(
        Request $request,
        callable $next,
        array $config = []
    ) {
        // Redirect authenticated users elsewhere.
        if ($this->session->validate()) {
            
            $role = $request->user()['role'] ?? 'customer';
            $path = config("auth.{$role}.dashboard");

            $this->session->redirect($path);
        }

        return $next($request);
    }
}