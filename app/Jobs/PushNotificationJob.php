<?php

namespace App\Jobs;

use App\Core\Container;
use App\Contracts\JobInterface;
use App\Notification\PushManager;

class PushNotificationJob implements JobInterface
{
    public PushManager $push;
    
    public string $target;
    public ?int $userId;
    public string $title;
    public string $body;
    public array $data;

    public function __construct(
        PushManager $push
    ) {
        $this->push = $push;
    }

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