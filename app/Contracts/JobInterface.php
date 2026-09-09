<?php

declare(strict_types=1);

namespace App\Contracts;

interface JobInterface
{
    public function handle(): void;

    public function setPayload(array $data): void;
}