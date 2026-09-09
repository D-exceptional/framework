<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Contracts\SessionInterface;
use App\Exceptions\MiddlewareException;

class CsrfMiddleware
{
    public function __construct(
        protected SessionInterface $session
    ) {}

    // =========================================
    // HANDLE CSRF CHECKS
    // =========================================
    public function handle(
        Request $request, 
        callable $next, 
        array $config = []
    ) {
        $method = $request->method();

        // Safe methods bypass CSRF
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        // Get token
        $token = $request->header('X-CSRF-TOKEN') ?? $request->input('_csrf_token');

        // Check token validity
        $isValidToken = $this->session->validateCsrf($token);
        if (!$isValidToken) {
            throw new MiddlewareException('Invalid or missing CSRF token', 419);
        }

        // Continue pipeline
        return $next($request);
    }
}
