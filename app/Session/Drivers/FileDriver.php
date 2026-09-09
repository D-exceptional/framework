<?php

declare(strict_types=1);

namespace App\Session\Drivers;

use App\Contracts\SessionInterface;
use App\Auth\JWT;

class FileDriver implements SessionInterface
{
    protected int $jwtExpireAt;

    public function __construct(
        protected JWT $jwt
    ) {
        $this->jwtExpireAt = config('jwt.expire_at');
    }

    public function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {

            /**
             * -----------------------------------------
             * Detect HTTPS
             * -----------------------------------------
             */
            $secure = (!empty($_SERVER['HTTPS']) 
                    && $_SERVER['HTTPS'] !== 'off');

            /**
             * -----------------------------------------
             * Custom session name
             * -----------------------------------------
             */
            session_name('app_session');

            /**
             * -----------------------------------------
             * Configure session cookie
             * -----------------------------------------
             */
            session_set_cookie_params([
                'lifetime' => 7200,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            /**
             * -----------------------------------------
             * Start session
             * -----------------------------------------
             */
            session_start();
        }
    }

    public function regenerate(): void
    {
        if (!isset($_SESSION['_regenerated'])) {

            session_regenerate_id(true);

            $this->store('_regenerated', time());
        }
    }

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

        $sessionId = bin2hex(random_bytes(32));

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

        if (isset($metadata['login_time']) && ($now - $metadata['login_time']) > $absoluteMax) {
            $this->destroy();
            return false;
        }

        if (isset($metadata['last_activity']) && ($now - $metadata['last_activity']) > $idleTimeout) {
            $this->destroy();
            return false;
        }

        $this->store('metadata', array_merge($metadata, ['last_activity' => $now]));

        return true;
    }

    public function destroy(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function store(
        string $key,
        mixed $value
    ): void {

        $_SESSION[$key] = $value;
    }

    public function retrieve(
        string $key
    ): mixed {

        return $_SESSION[$key] ?? null;
    }

    public function terminate(
        string $key
    ): void {

        unset($_SESSION[$key]);
    }

    public function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public function role(): ?string
    {
        return strtolower($_SESSION['user']['role'] ?? null);
    }

    public function authorize(
        string $role
    ): bool {

        return $this->check() && $this->role() === strtolower($role);
    }

    public function token(): ?string
    {
        $metadata = $this->retrieve('metadata');

        return $metadata['_csrf_token'] ?? null;
    }

    public function tokenSet(): bool
    {
        $metadata = $this->retrieve('metadata');

        return isset($metadata['_csrf_token']);
    }

    public function validateCsrf(
        ?string $token
    ): bool {

        if (!$token) {
            return false;
        }

        $metadata = $this->retrieve('metadata');
        $sessionToken = $metadata['_csrf_token'] ?? null;

        if (!is_string($sessionToken)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public function redirect(
        string $url
    ): void {

        header("Location: $url");

        exit();
    }
}