<?php
namespace App\Services;

use Predis\Client;
use App\Core\Redis;
use App\Http\Response;

class TestService
{
    protected Response $response;
    protected Client $redisClient;
    protected Redis $redisManager;

    public function __construct(
        Response $response, 
        Redis $redisManager
    )
    {
        $this->response     = $response;
        $this->redisManager = $redisManager;
        $this->redisClient  = $this->redisManager->cache();
    }

    // =========================================
    // CHECK API WORKING STATUS
    // =========================================
    public function ping() {
        return $this->response->success('API connected successfully!');
    }

    // =========================================
    // TEST REDIS CONNECTION
    // =========================================
    public function beep(): ?array
    {
        try {

            $ping = (string) $this->redisClient->ping(); // PONG
            $this->redisClient->setex('test:key', 60, 'Hello Redis!');
            $value = $this->redisClient->get('test:key');
            $ttl   = $this->redisClient->ttl('test:key');

            return $this->response->success(
                'Redis connection successful',
                [
                    'ping' => $ping,
                    'value' => $value,
                    'ttl' => $ttl
                ]
            );
        } catch (\Throwable $e) {
            return $this->response->fail(
                'Redis connection failed',
                500,
                $e->getMessage()
            );
        }
    }
}
