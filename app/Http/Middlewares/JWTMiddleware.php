<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Auth\Jwt;
use App\Exceptions\MiddlewareException;

class JwtMiddleware
{
    public function __construct(
        protected Jwt $jwt
    ) {}

    public function handle(
        Request $request,
        callable $next,
        array $config = []
    ) {

        $token = $request->bearerToken();

        if (!$token) {
            throw new MiddlewareException(
                'Invalid or null token',
                401
            );
        }

        $payload = $this->jwt->verify($token);

        // Remove unnecessary claims
        unset(
            $payload['session_id'],
            $payload['iat'],
            $payload['exp']
        );

        // Attach authenticated user
        $request->setUser($payload);

        return $next($request);
    }
}