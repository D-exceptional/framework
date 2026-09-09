<?php

declare(strict_types=1);

namespace App\Routing;

class Route
{
    public function __construct(
        protected string $method,
        protected string $path,
        protected string $controller,
        protected string $action,
        protected array $middlewares = [],
        protected ?string $name = null,
        protected ?string $pattern = null
    ) {}

    // =========================================
    // GETTERS
    // =========================================

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function controller(): string
    {
        return $this->controller;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function middlewares(): array
    {
        return $this->middlewares;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function pattern(): ?string
    {
        return $this->pattern;
    }

    // =========================================
    // SETTERS
    // =========================================

    public function setPattern(string $pattern): void
    {
        $this->pattern = $pattern;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Determine whether the route has a name.
     */
    public function hasName(): bool
    {
        return $this->name !== null && $this->name !== '';
    }

    /**
     * Get route parameters defined in the URI.
     *
     * Example:
     *
     * /u/{username}
     *
     * returns:
     *
     * ['username']
     */
    public function parameters(): array
    {
        preg_match_all(
            '/\{([\w]+)\}/',
            $this->path,
            $matches
        );

        return $matches[1] ?? [];
    }

    // =========================================
    // CONVERT ROUTE OBJECT TO ARRAY
    // =========================================
    public function toArray(): array
    {
        return [
            'method'      => $this->method,
            'path'        => $this->path,
            'controller'  => $this->controller,
            'action'      => $this->action,
            'middlewares' => $this->middlewares,
            'name'        => $this->name,
            'pattern'     => $this->pattern,
        ];
    }

    // =========================================
    // CONVERT ROUTE ARRAY TO OBJECT
    // =========================================
    public static function toObject(array $data): self
    {
        return new self(
            method: $data['method'],
            path: $data['path'],
            controller: $data['controller'],
            action: $data['action'],
            middlewares: $data['middlewares'] ?? [],
            name: $data['name'] ?? null,
            pattern: $data['pattern'] ?? null
        );
    }
}