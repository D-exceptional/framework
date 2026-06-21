<?php

namespace App\Jobs;

use App\Core\Container;
use App\Contracts\JobInterface;
use App\Mail\MailManager;

class SimpleMailJob implements JobInterface
{
    public MailManager $mailer;
    
    public string $subject;
    public string $email;
    public string $message;

    public function __construct(
        MailManager $mailer
    ) {
        $this->mailer = $mailer;
    }

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