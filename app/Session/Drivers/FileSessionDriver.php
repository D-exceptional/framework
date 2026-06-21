<?php

namespace App\Session\Drivers;

use App\Contracts\SessionInterface;

class FileSessionDriver implements SessionInterface
{
    // =========================================
    // START SESSION
    // =========================================
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
            session_name('saas_session');

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

    // =========================================
    // REGENERATE SESSION ID
    // =========================================
    public function regenerate(): void
    {
        if (!isset($_SESSION['_regenerated'])) {

            session_regenerate_id(true);

            $this->store('_regenerated', time());
        }
    }

    // ================================================
    // SESSION LOGIN WITH REGENERATION AND CSRF TOKEN
    // ================================================
    public function login(
        array $user
    ): void {

        $this->regenerate();

        $this->store('user', $user);
        $this->store('role', strtolower($user['role'] ?? ''));
        $this->store('login_time', time());
        $this->store('last_activity', time());
        $this->store('_csrf_token', bin2hex(random_bytes(32)));
    }

    // ===================================================
    // CHECK SESSION VALIDITY AND REGENERATE IF NEEDED
    // ===================================================
    public function validate(
        int $absoluteMax = 7200,
        int $idleTimeout = 1800
    ): bool {

        $now = time();

        if (!$this->check()) {
            return false;
        }

        if (isset($_SESSION['login_time']) && ($now - $_SESSION['login_time']) > $absoluteMax) {
            $this->destroy();
            return false;
        }

        if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $idleTimeout) {
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
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    // =========================================
    // STORE DATA IN SESSION
    // =========================================
    public function store(
        string $key,
        mixed $value
    ): void {

        $_SESSION[$key] = $value;
    }

    // =========================================
    // RETRIEVE DATA FROM SESSION
    // =========================================
    public function retrieve(
        string $key
    ): mixed {

        return $_SESSION[$key] ?? null;
    }

    // =========================================
    // TERMINATE SESSION DATA
    // =========================================
    public function terminate(
        string $key
    ): void {

        unset($_SESSION[$key]);
    }

    // =========================================
    // CHECK SESSION VALIDITY
    // =========================================
    public function check(): bool
    {
        return isset($_SESSION['user']);
    }

    // =========================================
    // GET USER DATA FROM SESSION
    // =========================================
    public function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    // =========================================
    // GET USER ID FROM SESSION
    // =========================================
    public function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    // =========================================
    // GET USER ROLE FROM SESSION
    // =========================================
    public function role(): ?string
    {
        return strtolower($_SESSION['user']['role'] ?? null);
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
        return $_SESSION['_csrf_token'] ?? null;
    }

    // =========================================
    // CHECK IF CSRF TOKEN IS SET IN SESSION
    // =========================================
    public function tokenSet(): bool
    {
        return isset($_SESSION['_csrf_token']);
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

        if (!is_string($sessionToken)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
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