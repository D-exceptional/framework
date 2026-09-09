<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\JobInterface;
use App\Media\CloudinaryManager;

class CloudinaryJob implements JobInterface
{
    public string $url;

    public function __construct(
        public CloudinaryManager $cloudinary
    ) {}

    public function setPayload(array $data): void
    {
        $this->url = $data[0];
    }

    public function handle(): void
    {
        $this->cloudinary->delete(
            $this->url
        );
    }
}