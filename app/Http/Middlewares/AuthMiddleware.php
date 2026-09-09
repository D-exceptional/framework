<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Contracts\SessionInterface;
use App\Exceptions\MiddlewareException;

class AuthMiddleware
{
    public function __construct(
        protected SessionInterface $session
    ) {}

    // =========================================
    // HANDLE AUTH CHECK
    // =========================================
    public function handle(
        Request $request, 
        callable $next, 
        array $config = []
    ) {

        $isValidSession = $this->session->validate();
        if (!$isValidSession) {
            throw new MiddlewareException('Unauthorized', 401);
        }

        // User is authenticated 
        // Set the authenticated user in the request object
        // Continue to next middleware or controller
        $user = $this->session->user();
        if ($user) {
            $request->setUser($user);
        }

        // Continue pipeline
        return $next($request);
    }
}
