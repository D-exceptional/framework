<?php

declare(strict_types=1);

namespace App\Session\Drivers;

use App\Redis\RedisManager;
use App\Redis\RedisStore;
use App\Contracts\SessionInterface;
use App\Auth\JWT;

class RedisDriver extends RedisStore implements SessionInterface
{
    protected string $sessionId;
    protected int $ttl = 7200;
    protected int $jwtExpireAt;

    public function __construct(
        RedisManager $redis,
        protected JWT $jwt
    ) {
        parent::__construct(
            $redis->session()
        );

        $this->jwtExpireAt = config('jwt.expire_at');
    }

    public function start(): void
    {
        /**
         * -----------------------------------------
         * Detect HTTPS
         * -----------------------------------------
         */
        $secure =
            (!empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off');

        /**
         * -----------------------------------------
         * Validate existing session ID
         * -----------------------------------------
         */
        $sessionId = $_COOKIE['app_session'] ?? null;

        if (
            !$sessionId ||
            !preg_match('/^[a-f0-9]{64}$/', $sessionId)
        ) {

            $sessionId = bin2hex(random_bytes(32));
        }

        $this->sessionId = $sessionId;

        /**
         * -----------------------------------------
         * Set session cookie
         * -----------------------------------------
         */
        setcookie(
            'app_session',
            $this->sessionId,
            [
                'expires'  => time() + $this->ttl,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );

        /**
         * -----------------------------------------
         * Refresh Redis TTL
         * -----------------------------------------
         */
        $this->setExpire(
            $this->key(),
            $this->ttl
        );
    }

    protected function key(): string
    {
        return "session:{$this->sessionId}";
    }

    protected function data(): array
    {
        $data = $this->getValue($this->key());

        return $data ? json_decode($data, true) : [];
    }

    protected function save(
        array $data
    ): void {

        $this->setValue($this->key(), json_encode($data), $this->ttl);
    }

    public function regenerate(): void
    {
        $data   = $this->data();
        $oldKey = $this->key();

        $this->sessionId = bin2hex(random_bytes(32));

        /**
         * -----------------------------------------
         * Detect HTTPS
         * -----------------------------------------
         */
        $secure =
            (!empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off');

        setcookie(
            'app_session',
            $this->sessionId,
            [
               'expires'  => time() + $this->ttl,
               'path'     => '/',
               'domain'   => '', 
               'secure'   => $secure, 
               'httponly' => true,                    
               'samesite' => 'Strict'
            ]
        );

        $this->save($data);

        $this->deleteValue($oldKey);
    }

    // =========================================
    // BASIC LOGIN USING SESSION STORAGE
    // =========================================
    public function basicLogin(
        array $user
    ): void {

        $this->regenerate();

        $metadata = [
            'login_time'    => time(),
            'last_activity' => time(),
            '_csrf_token'   => bin2hex(random_bytes(32))
        ];

        $this->store('user', $user);
        $this->store('metadata', $metadata);
    }

    // =========================================
    // JWT-BASED LOGIN WITH SESSION BACKING
    // =========================================
    public function jwtLogin(
        array $user
    ): array {

        $this->regenerate();

        $sessionId = $this->sessionId;

        $token = $this->jwt->generate([
            'id'         => $user['id'],
            'name'       => $user['fullname'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'session_id' => $sessionId,
            'iat'        => time(),
            'exp'        => $this->jwtExpireAt
        ]);

        $metadata = [
            'login_time'    => time(),
            'last_activity' => time(),
            '_csrf_token'   => bin2hex(random_bytes(32))
        ];

        $this->store('user', $user);
        $this->store('metadata', $metadata);

        return ['token' => $token];
    }

    public function validate(
        int $absoluteMax = 7200,
        int $idleTimeout = 1800
    ): bool {

        $now = time();

        if (!$this->check()) {
            return false;
        }

        $metadata = $this->retrieve('metadata');

        if (($now - $metadata['login_time']) > $absoluteMax) {
            $this->destroy();
            return false;
        }

        if (($now - $metadata['last_activity']) > $idleTimeout) {
            $this->destroy();
            return false;
        }

        $this->store('metadata', array_merge($metadata, ['last_activity' => $now]));

        return true;
    }

    public function destroy(): void
    {
        $this->deleteValue($this->key());

        setcookie(
            'app_session',
            '',
            [
                'expires'  => time() - 3600,
                'path'     => '/',
            ]
        );
    }

    public function store(
        string $key,
        mixed $value
    ): void {

        $data       = $this->data();
        $data[$key] = $value;

        $this->save($data);
    }

    public function retrieve(
        string $key
    ): mixed {

        return $this->data()[$key] ?? null;
    }

    public function terminate(
        string $key
    ): void {

        $data = $this->data();

        unset($data[$key]);

        $this->save($data);
    }

    public function check(): bool
    {
        return !empty($this->retrieve('user'));
    }

    public function user(): ?array
    {
        return $this->retrieve('user') ?? null;
    }

    public function id(): ?int
    {
        return $this->retrieve('user')['id'] ?? null;
    }

    public function role(): ?string
    {
        return strtolower($this->retrieve('user')['role'] ?? '');
    }

    public function authorize(
        string $role
    ): bool {

        return $this->check() && $this->role() === strtolower($role);
    }

    public function token(): ?string
    {
        return $this->retrieve('metadata')['_csrf_token'] ?? null;
    }

    public function tokenSet(): bool
    {
        return !empty($this->retrieve('metadata')['_csrf_token']);
    }

    public function validateCsrf(
        ?string $token
    ): bool {

        if (!$token) {
            return false;
        }

        $sessionToken = $this->retrieve('metadata')['_csrf_token'] ?? null;

        return is_string($sessionToken) && hash_equals($sessionToken, $token);
    }

    public function redirect(
        string $url
    ): void {

        header("Location: $url");

        exit();
    }
}