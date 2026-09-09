<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\JobInterface;
use App\Mail\MailManager;

class SimpleMailJob implements JobInterface
{    
    public string $subject;
    public string $email;
    public string $message;

    public function __construct(
        public MailManager $mailer
    ) {}

    public function setPayload(array $data): void
    {
        $this->subject = $data[0];
        $this->email   = $data[1];
        $this->message = $data[2];
    }

    public function handle(): void
    {
        $this->mailer->sendSimpleMail(
            $this->subject, 
            $this->email, 
            $this->message
        );
    }
}