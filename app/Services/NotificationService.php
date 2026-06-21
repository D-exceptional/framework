<?php

namespace App\Services;

use App\Http\Response;
use App\Models\Notification;

class NotificationService
{
    protected Response $response;
    protected Notification $notificationModel;

    public function __construct(
        Response $response, 
        Notification $notificationModel
    )
    {
        $this->response          = $response;
        $this->notificationModel = $notificationModel;
    }

    // =========================================
    // CREATE NOTIFICATION
    // =========================================
    public function create(
        string $details,
        string $type,
        int $receiver
    ): array {

        $created = $this->notificationModel->create($details, $type, $receiver);
        if ($created === false) {
            return $this->response->fail('Failed to create notification', 500);
        }

        return $this->response->success('Notification created', 201);
    }

    // =========================================
    // COUNT ALL NOTIFICATIONS
    // =========================================
    public function countAll(): array
    {
        $count = $this->notificationModel->countAll();

        return $this->response->success('All notifications counted', ['count' => $count]);
    }

    // =========================================
    // COUNT ALL NOTIFICATIONS BY ID
    // =========================================
    public function countAllById(
        int $userId
    ): array {

        $count = $this->notificationModel->countAllById($userId);

        return $this->response->success('All notifications counted', ['count' => $count]);
    }

    // =========================================
    // COUNT UNREAD NOTIFICATIONS BY ID
    // =========================================
    public function countUnreadById(
        int $userId
    ): array {

        $count = $this->notificationModel->countUnreadById($userId);

        return $this->response->success('Unread notifications counted', ['count' => $count]);
    }

    // =========================================
    // GET UNREAD NOTIFICATIONS
    // =========================================
    public function getUnread(
        int $userId, 
        int $limit, 
        int $offset
    ): array {

        $notifications = $this->notificationModel->getUnreadById($userId, $limit, $offset);
        if ($notifications === false) {
           return $this->response->fail('Failed to fetch notifications', 400);
        }

        return $this->response->success('Unread notifications fetched', ['notifications' => $notifications]);
    }

    // =========================================
    // GET NOTIFICATIONS BY ID
    // =========================================
    public function fetchById(
        int $userId, 
        int $limit, 
        int $offset
    ): array {

        $notifications = $this->notificationModel->getAllById($userId, $limit, $offset);
        if ($notifications === false) {
           return $this->response->fail('Failed to fetch notifications', 400);
        }

        return $this->response->success('Notifications fetched', $notifications);
    }

    // =========================================
    // MARK NOTIFICATION AS READ
    // =========================================
    public function markAsRead(
        int $userId
    ): array {
        
        $marked = $this->notificationModel->markAsRead($userId);
        if ($marked === false) {
            return $this->response->fail('Failed to mark as read', 500);
        }
        
        return $this->response->success('Notification marked as read');
    }
}
