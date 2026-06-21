<?php
    namespace App\Http\Middlewares;

    use App\Http\Request;
    use App\Redis\RedisManager;
    use App\Redis\RedisStore;
    use App\Exceptions\MiddlewareException;

    class RateLimitMiddleware extends RedisStore
    {
        protected RedisManager $redis;
        protected int $limit  = 60;
        protected int $window = 60;

        public function __construct(
            RedisManager $redis
        )
        {
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
        ): array {

            $userId = $request->user()['id'];
            $ip     = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            $identifier = $userId ? "user:$userId" : "ip:$ip";
            $scope      = $config['scope'] ?? 'global';
            $key        = "rate_limit:$scope:$identifier";

            $count = $this->incrementValue($key, 1);

            if ($count === 1) {
                // first request, set expiry
                $this->setExpire($key, $this->window);
            }

            // optional: different limits for logged-in / anonymous
            $limit = $userId ? ($config['userLimit'] ?? $this->limit) : ($config['anonLimit'] ?? ($this->limit / 3));

            if ($count > $limit) {
                throw new MiddlewareException('Too many requests', 429, 'json', '/login');
            }

            // Continue pipeline
            return $next($request);
        }
    }
