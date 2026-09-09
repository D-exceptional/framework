<?php

declare(strict_types=1);

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use App\Models\Mail;
use App\Models\Notification;

class MailManager
{
    protected array $smtpConfig = [];

    public function __construct(
        protected Mail $mailModel,
        protected Notification $notificationModel
    ) {
        $this->smtpConfig = config('mail');
        
        date_default_timezone_set('Africa/Lagos');
    }

    // =====================================================
    // STANDARDIZED RESPONSE FORMAT
    // =====================================================
    private function report(
        bool $success,
        string $message,
        string $code = 'OK',
        array $context = []
    ): array {

        return [
            'success' => $success,
            'code'    => $code,
            'message' => $message,
            'context' => $context
        ];
    }

    // =====================================================
    // BUILD MAILER INSTANCE
    // =====================================================
    private function buildMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->SMTPDebug  = 0;
        $mail->Host       = $this->smtpConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->smtpConfig['username'];
        $mail->Password   = $this->smtpConfig['password'];
        $mail->SMTPSecure = $this->smtpConfig['secure'];
        $mail->Port       = $this->smtpConfig['port'];

        // From headers
        $mail->setFrom(
            $this->smtpConfig['fromEmail'],
            $this->smtpConfig['fromName']
        );

        // Content settings
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        return $mail;
    }

    // =====================================================
    // LOAD MAIL TEMPLATE
    // =====================================================
    private function loadTemplate(
        string $message
    ): string {

        $templatePath = dirname(__DIR__, 2)
            . "/app/Mail/Templates/mail.php";

        if (!file_exists($templatePath)) {
            throw new \RuntimeException(
                'Mail template not found'
            );
        }

        $emailBody = file_get_contents($templatePath);

        return str_replace(
            '{{message}}',
            $message,
            $emailBody
        );
    }

    // =====================================================
    // SEND SIMPLE MAIL
    // =====================================================
    public function sendSimpleMail(
        string $subject,
        string $email,
        string $message
    ): array {

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = $this->report(
                false,
                'Invalid email address',
                'INVALID_EMAIL',
                ['email' => $email]
            );

            $this->writeLog($error);

            throw new \RuntimeException(
                "Invalid email address: {$email}"
            );
        }

        try {

            $mail = $this->buildMailer();

            $mail->addAddress($email);

            $mail->Subject = $subject;
            $mail->Body    = $this->loadTemplate($message);

            $mail->send();

            $response = $this->report(
                true,
                'Email sent successfully',
                'MAIL_SENT',
                ['email' => $email]
            );

            $this->writeLog($response);

            return $response;

        } catch (Exception $e) {

            $error = $this->report(
                false,
                $e->getMessage(),
                'SMTP_ERROR',
                ['email' => $email]
            );

            $this->writeLog($error);

            return $error;

        } catch (\Throwable $e) {

            $error = $this->report(
                false,
                $e->getMessage(),
                'SYSTEM_ERROR',
                ['email' => $email]
            );

            $this->writeLog($error);

            return $error;
        }
    }

    // =====================================================
    // SEND ADVANCED MAIL
    // =====================================================
    public function sendAdvancedMail(
        array $payload,
        bool $hasAttachment = false
    ): array {

        try {

            $mail = $this->buildMailer();

            $mail->addAddress(
                $payload['mail_receiver']
            );

            // Attachments
            if (
                $hasAttachment
                && isset($payload['filePath']) 
                && file_exists($payload['filePath'])
            ) {

                $mail->addAttachment(
                    $payload['filePath']
                );
            }

            $mail->Subject = $payload['mail_subject'];
            $mail->Body    = $payload['mail_message'];

            $mail->send();

            $response = $this->report(
                true,
                'Advanced mail sent successfully',
                'MAIL_SENT',
                [
                    'email' => $payload['mail_receiver']
                ]
            );

            $this->writeLog($response);

            return $response;

        } catch (Exception $e) {

            $error = $this->report(
                false,
                $e->getMessage(),
                'SMTP_ERROR',
                [
                    'email' => $payload['mail_receiver']
                ]
            );

            $this->writeLog($error);

            return $error;

        } catch (\Throwable $e) {

            $error = $this->report(
                false,
                $e->getMessage(),
                'SYSTEM_ERROR',
                [
                    'email' => $payload['mail_receiver']
                ]
            );

            $this->writeLog($error);

            return $error;
        }
    }

    // =====================================================
    // SEND BULK MAIL
    // =====================================================
    public function sendBulkMail(
        array $recipients,
        bool $hasAttachment = false,
        string $type = 'Text'
    ): void {

        $errors = [];

        $successful = 0;

        if (count($recipients) === 0) {

            $errors[] = [
                'type'    => 'SYSTEM_ERROR',
                'email'   => null,
                'message' => 'No recipients found'
            ];

            $this->writeLog($errors);

            throw new \RuntimeException(
                "No recipients found for this batch mailing process."
            );
        }

        $batches = array_chunk($recipients, 10);

        foreach ($batches as $index => $batch) {

            foreach ($batch as $payload) {

                // Save mail record
                $mailCreated = $this->mailModel->createMail(
                    $payload['mail_type'],
                    $payload['mail_subject'],
                    $payload['mail_sender'],
                    $payload['mail_receiver'],
                    $payload['mail_message'],
                    $payload['mail_filename'],
                    $payload['mail_extension']
                );

                if (!$mailCreated) {

                    $errors[] = [
                        'type'    => 'MAIL_RECORD_ERROR',
                        'email'   => $payload['mail_receiver'],
                        'message' => 'Failed to save mail record'
                    ];

                    continue;
                }

                // Send mail
                $result = $type === 'Text'

                    ? $this->sendSimpleMail(
                        $payload['mail_subject'],
                        $payload['mail_receiver'],
                        $payload['mail_message']
                    )

                    : $this->sendAdvancedMail(
                        $payload,
                        $hasAttachment
                    );

                if (!$result['success']) {

                    $errors[] = [
                        'type'    => $result['code'],
                        'email'   => $payload['mail_receiver'],
                        'message' => $result['message']
                    ];

                    continue;
                }

                $successful++;
            }

            // Prevent SMTP flooding
            if ($index < count($batches) - 1) {
                sleep(2);
            }
        }

        // Log bulk mail operation result
        $response = [
            'success' => empty($errors),

            'message' => empty($errors)
                ? 'All mails sent successfully'
                : 'Some mails failed to send',

            'summary' => [
                'total'      => count($recipients),
                'successful' => $successful,
                'failed'     => count($errors)
            ],

            'errors' => $errors
        ];

        $this->writeLog($response);
    }

    // =====================================================
    // LOG MAIL ERRORS
    // =====================================================
    private function writeLog(
        mixed $data
    ): void {

        $timestamp = date('Y-m-d H:i:s');

        $message = is_array($data)
            ? json_encode($data, JSON_PRETTY_PRINT)
            : (string) $data;

        $logFile = dirname(__DIR__, 2)
            . '/storage/logs/mail-manager.log';

        $directory = dirname($logFile);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $result = file_put_contents(
            $logFile,
            "[{$timestamp}] {$message}" . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($result === false) {
            throw new \RuntimeException(
                "Unable to write mailer log: {$logFile}"
            );
        }
    }
}