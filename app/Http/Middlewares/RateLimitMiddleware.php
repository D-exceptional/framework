<?php
    namespace App\Http\Middlewares;

    use App\Http\Request;
    use App\Contracts\CacheInterface;
    use App\Exceptions\MiddlewareException;

    class RateLimitMiddleware
    {
        protected CacheInterface $cache;
        protected int $maxAttempts = 60;
        protected int $window      = 60; // seconds

        public function __construct(CacheInterface $cache) 
        {
            $this->cache = $cache;
        }

        // =========================================
        // HANDLE BASIC RATE LIMITING
        // =========================================
        public function handle(
            Request $request, 
            callable $next, 
            array $config = []
        ) {

            if (!function_exists('apcu_fetch')) {
                // Re-enable this when APCu is available, for now we just skip rate limiting if it's not present
                // throw new MiddlewareException('APCu is not enabled', 505, 'json', '/login');

                // Fallback for localhost/dev
                return $next($request);
            }

            $userId = $request->user()['id'];
            $userIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            $identifier = $userId ? "user:$userId" : "ip:$userIp";
            $scope      = $config['scope'] ?? 'global';
            $key        = "rate:{$scope}:{$identifier}";

            $result = $this->check($userId, $key, $config);
            if (!$result['ok']) {
                throw new MiddlewareException($result['message'], $result['status'], 'json', '/login');
            }

            // Continue pipeline
            return $next($request);
        }

        // =========================================
        // CHECK REQUEST WINDOW COUNT
        // =========================================
        public function check(
            ?int $userId, 
            string $key, 
            array $options
        ): array {

            $success = false;
            $now     = time();
            $data    = $this->cache->get($key);

            if (!$success || $now > $data['reset_at']) {
                $this->cache->set($key, [
                    'attempts' => 1,
                    'reset_at' => $now + $this->window
                ], $this->window);

                return $this->success();
            }

            // optional: different limits for logged-in / anonymous
            $limit = !is_null($userId) ? ($options['userLimit'] ?? $this->maxAttempts) : ($options['anonLimit'] ?? ($this->maxAttempts / 3));

            if ($data['attempts'] >= $limit) {
                $remainingSeconds = max(0, $data['reset_at'] - $now);

                $minutes = intdiv($remainingSeconds, 60);
                $seconds = $remainingSeconds % 60;

                $message = $minutes > 0
                    ? "Rate limit exceeded. Try again in {$minutes} minute" . ($minutes > 1 ? 's' : '') .
                    ($seconds > 0 ? " {$seconds} second" . ($seconds > 1 ? 's' : '') : '')
                    : "Rate limit exceeded. Try again in {$seconds} second" . ($seconds > 1 ? 's' : '');

                return $this->fail($message, $remainingSeconds);
            }

            $data['attempts']++;
            $this->cache->set($key, $data, $data['reset_at'] - $now);

            return $this->success();
        }

        // =========================================
        // SUCCESS MESSAGE HELPER
        // =========================================
        protected function success(): array
        {
            return [
                'ok'       => true,
                'status'   => 200,
                'message'  => 'Limit validated',
                'action'   => 'continue',
                'redirect' => null,
            ];
        }

        // =========================================
        // FAILURE MESSAGE HELPER
        // =========================================
        protected function fail(
            string $message, 
            int $retry
        ): array {

            return [
                'ok'       => false,
                'status'   => 429,
                'message'  => $message,
                'action'   => 'json',
                'redirect' => null,
                'retry'    => $retry,
            ];
        }
    }
