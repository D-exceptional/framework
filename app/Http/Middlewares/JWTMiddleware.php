<?php

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Redis\RedisManager;
use App\Redis\RedisStore;
use App\Auth\JWT;
use App\Exceptions\MiddlewareException;

class AuthMiddleware extends RedisStore
{
    protected RedisManager $redis;
    protected JWT $jwt;

    public function __construct(
        RedisManager $redis,
        JWT $jwt
    ) {
        parent::__construct(
            $redis->session()
        );

        $this->jwt = $jwt;
    }

    // =========================================
    // HANDLE AUTH CHECK
    // =========================================
    public function handle(
        Request $request, 
        callable $next, 
        array $config = []
    ) {

        /*
        // $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        // $header = $request->headers();

        preg_match('/Bearer\s(\S+)/', $header, $matches)

        if (!preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            throw new MiddlewareException('Unauthorized', 401, 'redirect', '/login');
        }

        $token = $matches[1];
        */

        $token = $request->bearerToken();

        try {
            $payload = $this->jwt->verify($token);

            $sessionId = $payload['session_id'];

            $session = $this->getValue("session:$sessionId");

            if (!$session) {
                throw new MiddlewareException('Session expired', 401, 'redirect', '/login');
            }

            // Role check (if provided)
            if (isset($config['role'])) {
                $role = $payload['role'];

                $allowed = (array) $config['role'];

                if (!in_array($role, $allowed, true)) {
                    throw new MiddlewareException('Access denied', 403, 'json', '/login');
                }
            }

            // Attach authenticated user
            $request->setUser($payload);

        } catch (\Exception $e) {

            throw new MiddlewareException($e->getMessage(), 401, 'redirect', '/login');
        }

        return $next($request);
    }
}