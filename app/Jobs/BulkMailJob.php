<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\JobInterface;
use App\Mail\MailManager;

class BulkMailJob implements JobInterface
{
    public array $recipients;
    public bool $hasFile;
    public string $type;

    public function __construct(
        public MailManager $mailer
    ) {}

    public function setPayload(array $data): void
    {
        $this->recipients = $data[0];
        $this->hasFile    = $data[1];
        $this->type       = $data[2];
    }

    public function handle(): void
    {
        $this->mailer->sendBulkMail(
            $this->recipients, 
            $this->hasFile, 
            $this->type
        );
    }
}