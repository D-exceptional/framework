<?php

namespace App\Jobs;

use App\Core\Container;
use App\Contracts\JobInterface;
use App\Media\CloudinaryManager;

class CloudinaryJob implements JobInterface
{
    public CloudinaryManager $cloudinary;
    
    public string $url;

    public function __construct(
        CloudinaryManager $cloudinary
    ) {
        $this->cloudinary = $cloudinary;
    }

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