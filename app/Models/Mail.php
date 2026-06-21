<?php
namespace App\Models;

class Mail extends Model
{
    protected string $table = 'mailbox';

    // =========================================
    // CREATE MAIL
    // =========================================
    public function createMail(
        string $type, 
        string $subject, 
        string $sender, 
        string $receiver, 
        string $message,
        string $filename,
        string $extension
    ): bool {
        
        return $this->query()
            ->insert([
                'mail_type'      => $type,
                'mail_subject'   => $subject,
                'mail_sender'    => $sender,
                'mail_receiver'  => $receiver,
                'mail_message'   => $message,
                'mail_filename'  => $filename,
                'mail_extension' => $extension
            ]);
    }

    // =========================================
    // COUNT INBOX
    // =========================================
    public function countInbox(
        string $email
    ): int {

        return $this->query()
            ->where('mail_receiver', '=', $email)
            ->count();
    }

    // =========================================
    // COUNT OUTBOX
    // =========================================
    public function countOutbox(
        string $name
    ): int {

        return $this->query()
            ->where('mail_sender', '=', $name)
            ->count();
    }

    // =========================================
    // FETCH INBOX LOGS
    // =========================================
    public function getInbox(
        ?string $email = null, 
        int $page = 1, 
        int $limit = 20
    ): ?array {

        $mails = $this->query()
            ->where('mail_receiver', '=', $email)
            ->orderBy('created_at', 'DESC')
            ->paginate($page, $limit)
            ->get();

        $total = $this->countInbox($email);

        return $this->format($mails, $total, $page, $limit);
    }

    // =========================================
    // FETCH OUTBOX LOGS
    // =========================================
    public function getOutbox(
        ?string $name = null, 
        int $page = 1, 
        int $limit = 20
    ): ?array {

        $mails = $this->query()
            ->where('mail_sender', '=', $name)
            ->orderBy('created_at', 'DESC')
            ->paginate($page, $limit)
            ->get();

        $total = $this->countOutbox($name);

        return $this->format($mails, $total, $page, $limit);
    }

    // =========================================
    // GET MAIL 
    // =========================================
    public function getMail(
        int $mailId
    ): ?array {

        return $this->query()
            ->where('mail_id', '=', $mailId)
            ->first();
    }

    // =========================================
    // DELETE MAIL 
    // =========================================
    public function deleteMail(
        int $mailId
    ): ?array {

        return $this->query()
            ->where('mail_id', '=', $mailId)
            ->delete();
    }

    // =========================================
    // CHECK EMAIL EXISTENCE
    // =========================================
    public function checkMail(
        string $email
    ): bool
    {
        $stmt = $this->executeQuery(
            "SELECT 1 FROM mail_list WHERE email = ? LIMIT 1",
            [$email]
        );

        return $stmt->fetchColumn() !== false;
    }

    // =========================================
    // SUBSCRIBE EMAIL
    // =========================================
    public function subscribeMail(
        string $email
    ): bool
    {
        return $this->executeQuery(
            "INSERT INTO mail_list (email) VALUES (?)",
            [$email]
        );
    }

    // =========================================
    // VENDOR MAIL STATS
    // =========================================
    public function getVendorMailStats(
        string $email, 
        string $name
    ): ?array
    {
        return [
            'inbox'  => $this->countInbox($email),
            'outbox' => $this->countOutbox($name),
        ];
    }

    // =========================================
    // ADMIN MAIL STATS
    // =========================================
    public function getAdminMailStats(
        string $email, 
        string $name
    ): ?array
    {
        return [
            'inbox'  => $this->countInbox($email),
            'outbox' => $this->countOutbox($name),
        ];
    }

    // =========================================
    // RESULT PAGINATION HELPER
    // =========================================
    private function format(
        array $data, 
        int $total, 
        int $page, 
        int $limit
    ): ?array
    {
        // Calculate start and end item numbers
        $start = ($page - 1) * $limit + 1;
        $end   = min($page * $limit, $total); // ensures it doesn’t exceed total

        return [
            'mails'         => $data,
            'total'         => $total,
            'page'          => $page,
            'per_page'      => $limit,
            'total_pages'   => ceil($total / $limit),
            'display_range' => "{$start}-{$end}/{$total}" // e.g. "1-5/200"
        ];
    }
}
