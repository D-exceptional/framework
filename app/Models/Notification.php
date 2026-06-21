<?php

namespace App\Models;

class Notification extends Model
{
    protected string $table = 'general_notifications';

    // =========================================
    // CREATE NOTIFICATION
    // =========================================
    public function create(
        string $details, 
        string $type, 
        int $receiver
    ): bool {
        
        return $this->query()
            ->insert([
                'notification_details'  => $details,
                'notification_type'     => $type,
                'notification_receiver' => $receiver,
            ]);
    }

    // =========================================
    // COUNT ALL NOTIFICATIONS
    // =========================================
    public function countAll()
    : int {

        return $this->query()
            ->count();
    }

    // =========================================
    // COUNT NOTIFICATIONS BY ID
    // =========================================
    public function countAllById(
        int $userId
    )
    : int {

        return $this->query()
            ->where('notification_receiver', '=', $userId)
            ->count();
    }

    // =========================================
    // COUNT UNREAD NOTIFICATIONS BY ID
    // =========================================
    public function countUnreadById(
        int $userId
    )
    : int {

        return $this->query()
            ->where('notification_receiver', '=', $userId)
            ->where('notification_status', '=', 'Unread')
            ->count();
    }

    // =========================================
    // COUNT UNREAD NOTIFICATIONS GROUPED BY ID
    // =========================================
    public function countUnreadGroupedById(
        int $userId
    )
    : int {

        return $this->query()
            ->where('notification_receiver', '=', $userId)
            ->where('notification_status', '=', 'Unread')
            ->groupBy('notification_type')
            ->count();
    }

    // =========================================
    // COUNT NOTIFICATIONS BY TYPE
    // =========================================
    public function countByTypeWithLastDate(
        string $type, 
        int $userId
    ): ?array
    {
        // --- First query: count unread notifications by type --- //
        $count = $this->query()
            ->where('notification_type', '=', $type)
            ->where('notification_receiver', '=', $userId)
            ->where('notification_status', '=', 'Unread')
            ->count();

        // --- Second query: latest unseen incoming_mail date --- //
        $lastDate = $this->query()
            ->select(['created_at'])
            ->where('notification_type', '=', $type)
            ->where('notification_receiver', '=', $userId)
            ->where('notification_status', '=', 'Unread')
            ->orderBy('notification_id', 'DESC')
            ->first();

        // Return both in one response
        return [
            'count'     => $count,
            'last_date' => $lastDate ?: null
        ];
    }

    // =========================================
    // GET UNREAD NOTIFICATIONS BY ID
    // =========================================
    public function getUnreadById(
        ?int $userId = null, 
        int $page = 1,
        int $limit = 20
    ): ?array
    {

        return $this->query()
            ->where('notification_receiver', '=', $userId)
            ->where('notification_status', '=', 'Unread')
            ->orderBy('created_at', 'DESC')
            ->paginate($page, $limit)
            ->get();
    }

    // =========================================
    // GET ALL NOTIFICATIONS BY ID
    // =========================================
    public function getAllById(
        ?int $userId = null, 
        int $page = 1,
        int $limit = 20
    ): ?array
    {

        return $this->query()
            ->where('notification_receiver', '=', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate($page, $limit)
            ->get();
    }

    // =========================================
    // MARK NOTIFICATION AS READ
    // =========================================
    public function markAsRead(
        int $userId
    ): bool
    {

        return $this->query()
            ->where('notification_receiver', '=', $userId)
            ->update(['notification_status' => 'Read']);
    }

    // =========================================
    // FETCH NOTIFICATION STATS
    // =========================================
    public function getNotificationStats(
        int $userId
    ): ?array
    {
        return [
            'all'    => $this->countAllById($userId),
            'unread' => $this->countUnreadById($userId),
        ];
    }
}
