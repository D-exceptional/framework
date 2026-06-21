<?php

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\NotificationService;

class NotificationController 
{
    protected Response $response;
    protected NotificationService $service;

    public function __construct(Response $response, NotificationService $service)
    {
        $this->response = $response;
        $this->service  = $service;
    }

    // =========================================
    // CREATE NOTIFICATION
    // =========================================
    public function create(Request $request)
    {
        $result = $this->service->create(
            $request->input('details'),
            $request->input('type'),
            (int) $request->input('receiver')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // COUNT ALL NOTIFICATIONS
    // =========================================
    public function countAll()
    {
        $result = $this->service->countAll();
        return $this->response->flash($result);
    }

    // =========================================
    // COUNT ALL NOTIFICATIONS BY ID
    // =========================================
    public function countAllById(Request $request)
    {
        $userId = $request->user()['id'];
        $result = $this->service->countAllById($userId);
        return $this->response->flash($result);
    }

    // =========================================
    // COUNT UNREAD NOTIFICATIONS BY ID
    // =========================================
    public function countUnreadById(Request $request)
    {
        $userId = $request->user()['id'];
        $result = $this->service->countUnreadById($userId);
        return $this->response->flash($result);
    }

    // =========================================
    // GET UNREAD NOTIFICATIONS
    // =========================================
    public function getUnread(Request $request)
    {
        $limit  = (int) $request->input('limit') ?? 20;
        $offset = (int) $request->input('offset') ?? 0;

        $userId = $request->user()['id'];
        $result = $this->service->getUnreadById($userId, $limit, $offset);
        return $this->response->flash($result);
    }

    // =========================================
    // GET NOTIFICATIONS BY ID
    // =========================================
    public function fetchById(Request $request)
    {
        $limit  = (int) $request->input('limit') ?? 20;
        $offset = (int) $request->input('offset') ?? 0;

        $userId = $request->user()['id'];
        $result = $this->service->fetchById($userId, $limit, $offset);
        return $this->response->flash($result);
    }

    // =========================================
    // MARK NOTIFICATION AS READ
    // =========================================
    public function markAsRead(Request $request)
    {
        $userId = $request->user()['id'];
        $result = $this->service->markAsRead($userId);
        return $this->response->flash($result);
    }
}
