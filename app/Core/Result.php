<?php

declare(strict_types=1);

namespace App\Core;

final class Result
{
    public function __construct(
        protected bool $ok = false,
        protected int $status = 200,
        protected ?string $message = null,
        protected mixed $data = [],
        protected mixed $error = null
    ) {}

    /**
     * Create a successful result.
     */
    public function success(
        ?string $message = null,
        mixed $data = [],
        int $status = 200
    ): self {
        return new self(
            true,
            $status,
            $message,
            $data
        );
    }

    /**
     * Create an error result.
     */
    public function error(
        ?string $message = null,
        int $status = 400,
        mixed $error = null
    ): self {
        return new self(
            false,
            $status,
            $message,
            null,
            $error
        );
    }

    public function ok(): bool
    {
        return $this->ok;
    }

    public function failed(): bool
    {
        return !$this->ok;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function message(): ?string
    {
        return $this->message;
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function errorData(): mixed
    {
        return $this->error;
    }

    public function toArray(): array
    {
        return [
            'ok'      => $this->ok,
            'status'  => $this->status,
            'message' => $this->message,
            'data'    => $this->data,
            'error'   => $this->error
        ];
    }
}