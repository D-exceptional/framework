<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\JobInterface;
use App\Notification\PushManager;

class PushNotificationJob implements JobInterface
{    
    public string $target;
    public ?int $userId;
    public string $title;
    public string $body;
    public array $data;

    public function __construct(
        public PushManager $push
    ) {}

    public function setPayload(array $data): void
    {
        $this->target = $data[0];
        $this->userId = $data[1];
        $this->title  = $data[2];
        $this->body   = $data[3];
        $this->data   = $data[4];
    }

    public function handle(): void
    {
        $this->push->sendPushNotification(
            $this->target, 
            $this->userId, 
            $this->title, 
            $this->body, 
            $this->data
        );
    }
}