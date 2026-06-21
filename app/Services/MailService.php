<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;  
use PHPMailer\PHPMailer\SMTP;

use App\Http\Response;
use App\Support\TextManager;
use App\Queue\Queue;
use App\Jobs\BulkMailJob;
use App\Models\Mail;
use App\Models\Notification;

class MailService
{
    protected Response $response;
    protected TextManager $textProcessor;
    protected Queue $queueManager;
    protected Mail $mailModel;
    protected Notification $notificationModel;
    private array $smtpConfig = [];
    private string $baseUrl;

    public function __construct(
        Response $response,
        TextManager $textProcessor,
        Queue $queueManager,
        Mail $mailModel,
        Notification $notificationModel
    )
    {
        $this->response          = $response;
        $this->textProcessor     = $textProcessor;
        $this->queueManager      = $queueManager;
        $this->mailModel         = $mailModel;
        $this->notificationModel = $notificationModel;

        $this->smtpConfig = [
            'host'      => $_ENV['MAIL_HOST'],
            'username'  => $_ENV['MAIL_USERNAME'],
            'password'  => $_ENV['MAIL_PASSWORD'],
            'fromEmail' => $_ENV['MAIL_ADDRESS'],
            'fromName'  => $_ENV['MAIL_SENDER'],
            'port'      => $_ENV['MAIL_PORT'],
            'secure'    => $_ENV['MAIL_SECURE']
        ];

        $this->baseUrl = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) ? 'http://localhost/projects/showcase/jobspot' : '';
    }

    // =========================================
    // COUNT INBOX
    // =========================================
    public function countInbox(
        string $email
    ): array {

        $count = $this->mailModel->countInbox($email);
        if ($count === null) return $this->response->fail('Failed to get count', 500);

        return $this->response->success('Inbox counted', ['count' => $count]);
    }

    // =========================================
    // COUNT OUTBOX
    // =========================================
    public function countOutbox(
        string $name
    ): array {

        $count = $this->mailModel->countOutbox($name);
        if ($count === null) return $this->response->fail('Failed to get count', 500);

        return $this->response->success('Outbox counted', ['count' => $count]);
    }

    // =========================================
    // GET INBOX MESSAGES
    // =========================================
    public function getInbox(
        string $email, 
        int $page
    ): array {

        $inbox = $this->mailModel->getInbox($email, $page);
        // if (count($inbox['mails']) === 0) return $this->response->fail('No inbox to fetch', 200);

        return $this->response->success('Inbox fetched', $inbox);
    }

    // =========================================
    // GET OUTBOX MESSAGES
    // =========================================
    public function getOutbox(
        string $name, 
        int $page
    ): array {

        $outbox = $this->mailModel->getOutbox($name, $page);
        // if (count($outbox['mails']) === 0) return $this->response->fail('No outbox to fetch', 200);

        return $this->response->success('Outbox fetched', $outbox);
    }

    // =========================================
    // GET SINGLE MAIL
    // =========================================
    public function getMail(
        int $id
    ): array {

        $mail = $this->mailModel->getMail($id);
        if ($mail === false) return $this->response->fail('Failed to fetch mail', 500);

        return $this->response->success('Mail fetched', ['mail' => $mail]);
    }

    // =========================================
    // DELETE MAIL
    // =========================================
    public function deleteMail(
        int $id
    ): array { 

        $deleted = $this->mailModel->deleteMail($id);
        if ($deleted === false) return $this->response->fail('Failed to delete mail', 500);
        
        return $this->response->success('Mail deleted successfully');
    }

    // =========================================
    // SUBSCRIBE TO MAIL LIST
    // =========================================
    public function subscribeMail(
        string $email
    ): array {  

        $check = $this->mailModel->checkMail($email);
        if ($check === false) {
           $subscribed = $this->mailModel->subscribeMail($email);

           if ($subscribed === false) {
             return $this->response->fail('Failed to subscribe mail', 500);
           }
           else {
            return $this->response->success('Mail subscribed successfully');
           }
        }
        else {
            return $this->response->success('Mail already subscribed');
        }
    }

    // =========================================
    // SEND BULK MAIL (ENTRY POINT)
    // =========================================
    public function sendBulk(
        array $recipients,
        string $subject,
        string $message,
        string $sender,
        bool $hasAttachment = false, 
        array $file = []
    )
    {
        return $this->processBulkMail($recipients, $subject, $message, $sender, $hasAttachment, $file);
    }

    // =========================================
    // PROCESS BULK MAIL DATA
    // =========================================
    private function processBulkMail(
        array $recipients,
        string $subject,
        string $message,
        string $sender,
        bool $hasAttachment = false, 
        array $file = []
    ): array {

        if (empty($recipients) || empty($subject) || empty($message) || empty($sender)) {
            return $this->response->fail('All fields must be filled up');
        }

        $type     = $hasAttachment ? 'Multimedia' : 'Text';
        $filename = 'None';
        $file_ext = 'None';
        $filePath = null;

        // Handle file upload if exists
        if ($hasAttachment && !empty($file['name'])) {
            $targetDir = dirname(__DIR__) . "/../attachments/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $filename = basename($file['name']);
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $tmp_name = $file['tmp_name'];

            $allowed_exts = ["jpeg", "png", "jpg", "pdf", "mp3", "mp4", "docx"];
            if (!in_array($file_ext, $allowed_exts)) {
                return $this->response->fail('Invalid file type. Allowed: .jpg, .jpeg, .png, .pdf, .mp3, .mp4, .docx');
            }

            $filePath = $targetDir . $filename;
            if (!move_uploaded_file($tmp_name, $filePath)) {
                return $this->response->fail('Failed to upload attachment');
            }
        }

        // Prepare messages
        $mailArray = [];

        foreach ($recipients as $receiver) {
            $fullname    = $receiver['name'] ?? 'User';
            $email       = $receiver['email'] ?? '';
            $recipientId = (int) $receiver['id'] ?? null;
            $member      = $receiver['member'] ?? null;
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

            $mailArray[] = [
                "mail_type"     => $type,
                "mail_subject"  => $subject,
                "mail_sender"   => $sender,
                "mail_receiver" => $email,
                "mail_message"  => $this->textProcessor->formatMailMessage(
                    "<b style='font-size: 20px;'>Dear {$fullname},</b><br><hr style='opacity:0;'>" . $message
                ),
                "mail_filename"  => $filename,
                "mail_extension" => $file_ext,
                "userId"         => $recipientId,
                "member"         => $member
            ];
        }

        if (empty($mailArray)) {
            return $this->response->fail('No valid email recipients found');
        }

        // Bulk email job dispatch
        $this->queueManager->dispatch(
            BulkMailJob::class,
            [
                $mailArray,
                $hasAttachment,
                $type
            ],
            'broadcast',
            0,
            3,
            120 // 2 minutes in seconds. You can increase or reduce the time
        );

        return $this->response->success('Message queued successfully');
    }
}
