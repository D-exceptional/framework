<?php

declare(strict_types=1);

namespace App\Contracts;

interface SessionInterface
{
    public function start(): void;

    public function regenerate(): void;

    public function basicLogin(array $user): void;

    public function jwtLogin(array $user): array;

    public function validate(int $absoluteMax = 7200, int $idleTimeout = 1800): bool;

    public function destroy(): void;

    public function store(string $key, mixed $value): void;

    public function retrieve(string $key): mixed;

    public function terminate(string $key): void;

    public function check(): bool;

    public function user(): ?array;

    public function id(): ?int;

    public function role(): ?string;

    public function authorize(string $role): bool;

    public function token(): ?string;

    public function tokenSet(): bool;

    public function validateCsrf(?string $token): bool;

    public function redirect(string $url): void;
}