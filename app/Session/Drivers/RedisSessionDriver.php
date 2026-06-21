<?php

namespace App\Session\Drivers;

use App\Redis\RedisManager;
use App\Redis\RedisStore;
use App\Contracts\SessionInterface;
use App\Auth\JWT;

class RedisSessionDriver extends RedisStore implements SessionInterface
{
    protected RedisManager $redis;
    protected JWT $jwt;
    protected string $sessionId;
    protected int $ttl = 7200;

    public function __construct(
        RedisManager $redis,
        JWT $jwt
    )
    {
        parent::__construct(
            $redis->session()
        );

        $this->jwt = $jwt;
    }

    // =========================================
    // START SESSION
    // =========================================
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

    // =========================================
    // GET SESSION KEY
    // =========================================
    protected function key(): string
    {
        return "session:{$this->sessionId}";
    }

    // =========================================
    // GET SESSION DATA
    // =========================================
    protected function data(): array
    {
        $data = $this->getValue($this->key());

        return $data ? json_decode($data, true) : [];
    }

    // =========================================
    // SAVE SESSION DATA
    // =========================================
    protected function save(
        array $data
    ): void {

        $this->setValue($this->key(), $this->ttl, json_encode($data));
    }

    // =========================================
    // REGENERATE SESSION ID
    // =========================================
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

        $this->del($oldKey);
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
            'exp'        => time() + 900 
        ]);

        $metadata = [
            'login_time'    => time(),
            'last_activity' => time(),
            '_csrf_token'   => bin2hex(random_bytes(32))
        ];

        // $this->store('user', $user);
        $this->store('jwt', $token);
        $this->store('metadata', $metadata);

        return ['token' => $token];
    }

    // =================================================
    // CHECK SESSION VALIDITY AND REGENERATE IF NEEDED
    // =================================================
    public function validate(
        int $absoluteMax = 7200,
        int $idleTimeout = 1800
    ): bool {

        $now = time();

        if (!$this->check()) {
            return false;
        }

        if (($now - $this->retrieve('login_time')) > $absoluteMax) {
            $this->destroy();
            return false;
        }

        if (($now - $this->retrieve('last_activity')) > $idleTimeout) {
            $this->destroy();
            return false;
        }

        $this->store('last_activity', $now);

        return true;
    }

    // =========================================
    // DESTROY SESSION
    // =========================================
    public function destroy(): void
    {
        $this->del($this->key());

        setcookie(
            'app_session',
            '',
            [
                'expires'  => time() - 3600,
                'path'     => '/',
            ]
        );
    }

    // =========================================
    // STORE DATA IN SESSION
    // =========================================
    public function store(
        string $key,
        mixed $value
    ): void {

        $data       = $this->data();
        $data[$key] = $value;

        $this->save($data);
    }

    // =========================================
    // RETRIEVE DATA FROM SESSION
    // =========================================
    public function retrieve(
        string $key
    ): mixed {

        return $this->data()[$key] ?? null;
    }

    // =========================================
    // TERMINATE SESSION DATA
    // =========================================
    public function terminate(
        string $key
    ): void {

        $data = $this->data();

        unset($data[$key]);

        $this->save($data);
    }

    // =========================================
    // CHECK IF USER IS LOGGED IN
    // =========================================
    public function check(): bool
    {
        return !empty($this->retrieve('user'));
    }

    // =========================================
    // GET USER INFO FROM SESSION
    // =========================================
    public function user(): ?array
    {
        return $this->retrieve('user');
    }

    // =========================================
    // GET USER ID FROM SESSION
    // =========================================
    public function id(): ?int
    {
        return $this->retrieve('user')['id'] ?? null;
    }

    // =========================================
    // GET USER ROLE FROM SESSION
    // =========================================
    public function role(): ?string
    {
        return strtolower($this->retrieve('user')['role'] ?? '');
    }

    // =========================================
    // AUTHORIZE USER BASED ON ROLE
    // =========================================
    public function authorize(
        string $role
    ): bool {

        return $this->check() && $this->role() === strtolower($role);
    }

    // =========================================
    // GET CSRF TOKEN FROM SESSION
    // =========================================
    public function token(): ?string
    {
        return $this->retrieve('_csrf_token');
    }

    // =========================================
    // CHECK IF CSRF TOKEN IS SET IN SESSION
    // =========================================
    public function tokenSet(): bool
    {
        return !empty( $this->retrieve('_csrf_token'));
    }

    // =========================================
    // VALIDATE CSRF TOKEN
    // =========================================
    public function validateCsrf(
        ?string $token
    ): bool {

        if (!$token) {
            return false;
        }

        $sessionToken = $this->retrieve('_csrf_token');

        return is_string($sessionToken) && hash_equals($sessionToken, $token);
    }

    // =========================================
    // REDIRECT TO A URL
    // =========================================
    public function redirect(
        string $url
    ): void {

        header("Location: $url");

        exit();
    }
}