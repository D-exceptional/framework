<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Redis\RedisManager;
use App\Redis\RedisStore;
use App\Exceptions\MiddlewareException;

class RateLimitMiddleware extends RedisStore
{
    protected int $limit  = 60;
    protected int $window = 60;

    public function __construct(
        RedisManager $redis
    ) {
        parent::__construct(
            $redis->rateLimiter()
        );
    }

    // =========================================
    // HANDLE REDIS RATE LIMITING
    // =========================================
    public function handle(
        Request $request, 
        callable $next, 
        array $config = []
    ): mixed {

        $userId = $request->user()['id'] ?? null;
        $ip     = $request->ip();

        $identifier = $userId ? "user:$userId" : "ip:$ip";
        $scope      = $config['scope'] ?? 'global';
        $key        = "rate_limit:$scope:$identifier";

        $count = $this->incrementValue($key, 1);

        // 2. FIX: Prevent the race condition by setting a TTL if none exists
        if ($this->getTtl($key) === -1) {
            $this->setExpire($key, $this->window);
        }

        // Optional: different limits for logged-in / anonymous
        $limit = $userId ? ($config['userLimit'] ?? $this->limit) : ($config['anonLimit'] ?? ($this->limit / 3));

        $remaining = max(0, $limit - $count);
        $reset     = max(0, $this->getTtl($key));

        if ($count > $limit) {

            $minutes = intdiv($reset, 60);
            $seconds = $reset % 60;

            $message = $minutes > 0
                ? "Rate limit exceeded. Try again in {$minutes} minute"
                    . ($minutes > 1 ? 's' : '')
                    . ($seconds > 0
                        ? " {$seconds} second" . ($seconds > 1 ? 's' : '')
                        : '')
                : "Rate limit exceeded. Try again in {$seconds} second"
                    . ($seconds > 1 ? 's' : '');

            throw new MiddlewareException(
                $message,
                429,
                [
                    'X-RateLimit-Limit'     => (int)$limit,
                    'X-RateLimit-Remaining' => (int)$remaining,
                    'X-RateLimit-Reset'     => (int)$reset,
                ]
            );
        }

        // Continue pipeline
        return $next($request);
    }
}
