<?php

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\TaskService;

class TaskController 
{
    protected Response $response;
    protected TaskService $service;

    public function __construct(Response $response, TaskService $service)
    {
        $this->response = $response;
        $this->service  = $service;
    }

    // =========================================
    // CREATE TASK
    // =========================================
    public function createTask(Request $request)
    {
        $result = $this->service->createTask(
            $request->input('name'),
            $request->input('description'),
            (int) $request->input('reward'),
            $request->input('start'),
            $request->input('end')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE TASK DETAILS
    // =========================================
    public function updateDetails(Request $request)
    {
        $result = $this->service->updateDetails(
            (int) $request->input('id'),
            $request->input('name'),
            $request->input('description'),
            (int) $request->input('reward'),
            $request->input('start'),
            $request->input('end'),
            $request->input('status'),
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE TASK STATUS
    // =========================================
    public function updateStatus(Request $request)
    {
        $result = $this->service->updateStatus(
            $request->input('status'),
            (int) $request->input('id'),
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE MATCHING TASKS STATUS
    // =========================================
    public function updateAll(Request $request)
    {
        $result = $this->service->updateAll(
           $request->input('status')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // DELETE TASK
    // =========================================
    public function deleteTask(Request $request)
    {
        $result = $this->service->deleteTask(
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET SINGLE TASK
    // =========================================
    public function findOne(Request $request)
    { 
        $result = $this->service->findOne(
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // LOAD USER TASKS
    // =========================================
    public function loadTask(Request $request)
    { 
        $userId = $request->user()['id'];
        $result = $this->service->loadTask(
            $userId, 
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET TASKS BY STATUS
    // =========================================
    public function findByStatus(Request $request)
    { 
        $result = $this->service->findByStatus(
            $request->input('status'), 
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // ATTEMPT TASK
    // =========================================
    public function attemptTask(Request $request)
    {                                                  
        $userId = $request->user()['id'];
        $result = $this->service->attemptTask(
            $request->input('link'), 
            (int) $request->input('id'),
            $userId
        );
        return $this->response->flash($result);
    }

    // =========================================
    // FINALIZE TASK ATTEMPT
    // =========================================
    public function finalizeAttempt(Request $request)
    { 
        $result = $this->service->finalizeAttempt(
            (int) $request->input('id'), 
            (int) $request->input('user'), 
            $request->input('status')
        );
        return $this->response->flash($result);
    }
 
    // =========================================
    // LOAD TASK ATTEMPTS
    // =========================================
    public function getAttempt(Request $request)
    { 
        $result = $this->service->getAttempt(
            (int) $request->input('id'), 
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }
      
    // =========================================
    // GET TASK DESCRIPTION
    // ========================================= 
    public function getDescription(Request $request)
    { 
        $result = $this->service->getDescription(
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }
}
  