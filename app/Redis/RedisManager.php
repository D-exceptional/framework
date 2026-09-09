<?php

declare(strict_types=1);

namespace App\Redis;

use Predis\Client;

use InvalidArgumentException;

class RedisManager
{
    protected array $connections = [];
    
    protected array $databases = [
        'cache'       => 0,
        'session'     => 1,
        'queue'       => 2,
        'rateLimiter' => 3,
    ];

    // =============================================
    // CREATE OR RETURN EXISTING REDIS CONNECTION
    // =============================================
    public function connection(
        string $name = 'cache'
    ): Client {

        if (!isset($this->databases[$name])) {
            throw new InvalidArgumentException(
                "Redis connection [{$name}] does not exist."
            );
        }

        // Reuse existing connection
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        // Get Redis configuration
        $options = config('redis');

        // Attach database index to options
        $options['database'] = $this->databases[$name];

        // Create new Redis client
        $this->connections[$name] = new Client($options);

        return $this->connections[$name];
    }

    // ========================================
    // CACHE REDIS CONNECTION
    // ========================================
    public function cache(): Client
    {
        return $this->connection('cache');
    }

    // ========================================
    // SESSION REDIS CONNECTION
    // ========================================
    public function session(): Client
    {
        return $this->connection('session');
    }

    // ========================================
    // QUEUE REDIS CONNECTION
    // ========================================
    public function queue(): Client
    {
        return $this->connection('queue');
    }

    // ========================================
    // RATE LIMITER REDIS CONNECTION
    // ========================================
    public function rateLimiter(): Client
    {
        return $this->connection('rateLimiter');
    }

    // ========================================
    // DYNAMICALLY CREATE CUSTOM CONNECTIONS
    // Example:
    // Redis->extend('socket', 4);
    // ========================================
    public function extend(
        string $name,
        int $database
    ): void {

        $this->databases[$name] = $database;
    }

    // ========================================
    // REMOVE CACHED CONNECTION
    // ========================================
    public function disconnect(
        string $name
    ): void {

        unset($this->connections[$name]);
    }

    // =========================================
    // DISCONNECT ALL REDIS CONNECTIONS
    // =========================================
    public function disconnectAll(): void
    {
        $this->connections = [];
    }
}