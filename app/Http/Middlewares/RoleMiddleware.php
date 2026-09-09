<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Exceptions\MiddlewareException;

class RoleMiddleware
{
    public function handle(
        Request $request,
        callable $next,
        array $config = []
    ) {
        // Get authenticated user
        $user = $request->user();

        if (!$user) {
            throw new MiddlewareException(
                'Unauthenticated',
                401
            );
        }

        // Get user role
        $role = $user['role'] ?? null;

        if ($role === null) {
            throw new MiddlewareException(
                'Invalid user role',
                403
            );
        }

        // Get allowed roles
        $allowed = (array) ($config['role'] ?? []);

        if (empty($allowed)) {
            throw new MiddlewareException(
                'No roles configured',
                500
            );
        }

        // Check authorization
        if (!in_array($role, $allowed, true)) {
            throw new MiddlewareException(
                'Access denied',
                403
            );
        }

        return $next($request);
    }
}