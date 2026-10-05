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
        /*
         * Determine the authentication role.
         */
        $role = $config['role'] ?? 'user';

        /*
         * Validate the current session.
         */
        $isValidSession = $this->session->validate();

        if (!$isValidSession) {

            $loginPath = config(
                "auth.{$role}.login"
            );

            if (!$loginPath) {
                throw new MiddlewareException(
                    "No login path configured for authentication role `{$role}`.",
                    500
                );
            }

            throw new MiddlewareException(
                'Unauthorized',
                401,
                action: 'redirect',
                redirect: $loginPath
            );
        }

        /*
         * Continue pipeline.
         */
        return $next($request);
    }
}