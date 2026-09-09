<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    protected array $get = [];
    protected array $post = [];
    protected array $files = [];
    protected array $headers = [];
    protected array $body = [];
    protected array $routeParams = [];
    protected ?array $user = null;

    protected string $method;
    protected string $uri;

    public function __construct()
    {
        $this->method  = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri     = $this->getUri();
        $this->get     = $_GET ?? [];
        $this->post    = $_POST ?? [];
        $this->files   = $_FILES ?? [];
        $this->headers = $this->parseHeaders();
        $this->body    = $this->parseBody();
        $this->user    = null; // Set this later based on authentication logic     
    }

    // =========================================
    // CORE REQUEST DATA
    // =========================================
    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    // =========================================
    // INPUT ACCESS
    // =========================================
    public function input(
        string $key, 
        mixed $default = null
    ): mixed {

        return $this->body[$key]
            ?? $this->post[$key]
            ?? $this->get[$key]
            ?? $default;
    }

    public function query(
        ?string $key = null, 
        $default = null
    ) {
        if ($key === null) {
            return $this->get;
        }

        return $this->get[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->body);
    }

    public function only(
        array $keys
    ): array {

        $data = $this->all();
        return array_intersect_key($data, array_flip($keys));
    }

    public function except(
        array $keys
    ): array {

        $data = $this->all();
        return array_diff_key($data, array_flip($keys));
    }

    // =========================================
    // HEADERS
    // =========================================
    public function header(
        string $key, 
        mixed $default = null
    ): mixed {

        return $this->headers[strtolower($key)] ?? $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    // =========================================
    // FILES
    // =========================================
    public function file(
        string $key
    ): mixed {

        return $this->files[$key] ?? []; // You can also use null for non-existent file instead of empty array
    }

    public function files(): array
    {
        return $this->files;
    }

    // =========================================
    // ROUTE PARAMETERS
    // =========================================
    public function setRouteParams(
        array $params
    ): void {

        $this->routeParams = $params;
    }

    /**
     * Get all route parameters.
     */
    public function params(): array
    {
        return $this->routeParams;
    }

    /**
     * Get a route parameter.
     */
    public function param(
        string $key, 
        mixed $default = null
    ): mixed {

        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Get a route parameter.
     */
    public function route(
        string $key, 
        mixed $default = null
    ): mixed {

        return $this->routeParams[$key] ?? $default;
    }

    // =========================================
    // USER CONTEXT 
    // =========================================
    public function setUser(
        array $user
    ): void {

        $this->user = $user;
    }

    public function user(): ?array
    {
        return $this->user;
    }

    // =========================================
    // USEFUL ADDITIONAL METHODS
    // =========================================
    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');
        if (!$header) {
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function expectsJson(): bool
    {
        return str_starts_with(
            $this->uri(),
            '/api'
        ) || str_contains(
            $this->header('Accept', ''),
            'application/json'
        );
    }

    public function isMethod(
        string $method
    ): bool {
        
        return strtoupper($this->method()) === strtoupper($method);
    }

    protected function getUri(): string
    {
        $uri = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        $basePath = rtrim(config('app.base_path', ''), '/');

        if (
            $basePath !== '' &&
            str_starts_with($uri, $basePath)
        ) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim($uri, '/');

        return $uri === '//' ? '/' : $uri;
    }

    protected function parseBody(): array
    {
        $method = $this->method();

        // =========================================
        // GET REQUESTS
        // =========================================
        if ($method === 'GET') {

            parse_str(
                $_SERVER['QUERY_STRING'] ?? '',
                $query
            );

            return $query;
        }

        // =========================================
        // CONTENT TYPE
        // =========================================
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // Remove charset if present
        $contentType = trim(
            explode(';', $contentType)[0]
        );

        // =========================================
        // JSON REQUESTS
        // =========================================
        if ($contentType === 'application/json') {

            $raw = file_get_contents('php://input');

            if (empty(trim($raw))) {
                return [];
            }

            $decoded = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE) {

                throw new \Exception(
                    'Malformed JSON: ' .
                    json_last_error_msg()
                );
            }

            return $decoded ?? [];
        }

        // =========================================
        // URL ENCODED FORMS
        // =========================================
        if (
            $contentType ===
            'application/x-www-form-urlencoded'
        ) {

            return $_POST ?? [];
        }

        // =========================================
        // MULTIPART FORM DATA
        // =========================================
        if (
            str_contains(
                $contentType,
                'multipart/form-data'
            )
        ) {

            return $_POST ?? [];
        }

        // =========================================
        // RAW BODY FALLBACK
        // =========================================
        $raw = file_get_contents('php://input');

        if (!empty(trim($raw))) {

            return [
                'raw' => $raw
            ];
        }

        return [];
    }

    // =========================================
    // HEADER PARSER
    // =========================================
    protected function parseHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}