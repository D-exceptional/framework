<?php

namespace App\Services;

use App\Http\Response;
use App\Support\TextManager;
use App\Queue\Queue;
use App\Jobs\SimpleMailJob;
use App\Jobs\PushNotificationJob;
use App\Support\CurrencyManager;
use App\Models\Task;
use App\Models\User;
use App\Models\Notification;
use App\Models\Wallet;

use Exception;

class TaskService
{
    protected Response $response;
    protected TextManager $textProcessor;
    protected Queue $queueManager;
    protected CurrencyManager $transaction;
    protected Task $taskModel;
    protected User $userModel;
    protected Notification $notificationModel;
    protected Wallet $walletModel;
    private string $baseUrl;

    public function __construct(
        Response $response, 
        TextManager $textProcessor, 
        Queue $queueManager,
        CurrencyManager $transaction,
        Task $taskModel, 
        User $userModel, 
        Notification $notificationModel,
        Wallet $walletModel
    )
    {
        $this->response          = $response;
        $this->textProcessor     = $textProcessor;
        $this->queueManager      = $queueManager;
        $this->transaction       = $transaction;
        $this->taskModel         = $taskModel;
        $this->userModel         = $userModel;
        $this->notificationModel = $notificationModel;
        $this->walletModel       = $walletModel;
        $this->baseUrl           = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) ? 'http://localhost/projects/showcase/jobspot' : '';
    }

    // =========================================
    // CREATE TASK
    // =========================================
    public function createTask(
        string $name,
        string $description,
        int $reward,
        string $start,
        string $end
    ): array {

        $name = $this->textProcessor->formatTitle($name);

        $created = $this->taskModel->createTask($name, $description, $reward, $start, $end);
        if ($created === false) {
            return $this->response->fail('Failed to create task', 500);
        }

        $adminMessage =  "
            Hello Admin, 
            <br> A new task was created on the platform!
            <br> Kindly review and take necessary actions. 
        ";

        $adminPushMessage = $this->textProcessor->formatPushMessage($adminMessage);

        $admins = $this->userModel->allByRole('Admin');
        
        foreach ($admins as $admin) {

            // Simple email job dispatch
            $this->queueManager->dispatch(
                SimpleMailJob::class,
                [
                    'New Task',
                    $admin['email'],
                    $adminMessage
                ],
                'emails'
            );

            // Push notification job dispatch
            $this->queueManager->dispatch(
                PushNotificationJob::class,
                [
                    'Single Admin',
                    $admin['user_id'],
                    'New Task',
                    $adminPushMessage,
                    ['url' => "{$this->baseUrl}/admin/", 'type' => 'task']
                ],
                'push'
            );
            
            $notification = $this->notificationModel->create($adminMessage, 'New Task', $admin['user_id']);
            if ($notification === false) {
                $this->logError("Failed to create notification for admin: {$admin['email']}");
            }
        }

        return $this->response->success('Task created successfully', [], 201);
    }

    // =========================================
    // UPDATE TASK DETAILS
    // =========================================
    public function updateDetails(
        int $id,
        string $name,
        string $description,
        int $reward,
        string $start,
        string $end,
        string $status
    ): array {

        $updated = $this->taskModel->updateDetails($id, $name, $description, $reward, $start, $end, $status);
        if ($updated === false) {
            return $this->response->fail('Failed to update task', 500);
        }

        return $this->response->success('Details updated successfully');
    }

    // =========================================
    // UPDATE TASK STATUS
    // =========================================
    public function updateStatus(
        string $status,
        int $id
    ): array {

        $updated = $this->taskModel->updateStatus($status, $id);
        if ($updated === false) {
            return $this->response->fail('Failed to update status', 500);
        }

        return $this->response->success('Status updated successfully');
    }

    // =========================================
    // UPDATE MATCHING TASKS STATUS
    // =========================================
    public function updateAll(
        string $status
    ): array {

        $date = date('Y-m-d H:i:s');

        $updated = $this->taskModel->updateAll($status, $date);
        if ($updated === false) {
            return $this->response->fail('Failed to update status', 500);
        }

        return $this->response->success('Status updated successfully');
    }

    // =========================================
    // DELETE TASK
    // =========================================
    public function deleteTask(
        int $id
    ): array {

        $deleted = $this->taskModel->deleteTask($id);
        if ($deleted === false) {
            return $this->response->fail('Failed to delete task', 505);
        }

        return $this->response->success('Task deleted successfully');
    }

    // =========================================
    // GET SINGLE TASK
    // =========================================
    public function findOne(
        int $id
    ): array { 

        $task = $this->taskModel->findOne($id);
        if ($task === false) {
            return $this->response->fail('Failed to fetch task', 505);
        }
        
        return $this->response->success('Task fetched successfully', ['task' => $task]);
    }

    // =========================================
    // LOAD TASKS
    // =========================================
    public function loadTask(
        int $userId, 
        int $page
    ): array { 

        $tasks = $this->taskModel->fetchTasks($userId, $page);
        if ($tasks === false) {
            return $this->response->fail('Failed to fetch tasks', 505);
        }
        
        return $this->response->success('Tasks fetched successfully', ['tasks' => $tasks]);
    }

    // =========================================
    // GET TASKS BY STATUS
    // =========================================
    public function findByStatus(
        string $status, 
        int $page
    ): array { 

        $tasks = $this->taskModel->findByStatus($status, $page);
        if ($tasks === false) {
            return $this->response->fail('Failed to fetch tasks', 505);
        }
       
        return $this->response->success('Tasks fetched successfully', ['tasks' => $tasks]);
    }

    // =========================================
    // ATTEMPT TASK
    // =========================================
    public function attemptTask(
        string $link, 
        int $taskId, 
        int $userId
    ): array { 

        $saved = $this->taskModel->attemptTask($link, $taskId, $userId);
        if ($saved === false) {
            return $this->response->fail('Failed to save attempt', 500);
        }
        
        return $this->response->success('Attempt saved successfully');
    }

    // =========================================
    // FINALIZE TASK ATTEMPT
    // =========================================
    public function finalizeAttempt(
        int $taskId, 
        int $userId, 
        string $status
    ): array {

        $updated = $this->taskModel->finalizeAttempt($taskId, $userId, $status);
        if ($updated === false) {
            return $this->response->fail('Failed to update status', 500);
        }

        $taskDetails = $this->taskModel->findOne($taskId);
        $taskReward = $taskDetails['task_reward'];

        $processedReward = $this->transaction->format((float)$taskReward);

        $taskWallets = ['wallet_task', 'wallet_task_backup'];

        foreach ($taskWallets as $table) {
            $this->walletModel->creditWallet($table, $taskReward, $userId);
        }

        $userDetails = $this->userModel->findById($userId);
        $userName    = $userDetails['fullname'];
        $userEmail   = $userDetails['email'];
        $userRole    = $userDetails['user_role'];

        $statusMessages = [
            'Approved' => "
                Hi <b>{$userName}</b>, 
                <br> Great news! 🎉 Your latest attempt has been <b>approved</b>. 
                <br> A total of {$processedReward} has been credited to your task wallet.
                <br> Login to your dashboard via this link: <b><a href='{$this->baseUrl}/login'>Visit Dashboard</a></b> to confirm.
                <br> We hope to see more attempts from you!
            ",
        ];

        // Fallback in case of unknown status
        $message = $statusMessages[$status] ?? "
            Hi <b>{$userName}</b>, 
            <br> There has been an update to your latest task attempt. 
            <br> Please check your dashboard for more details.
        ";

        $mail = [
            'subject' => 'Task Approved',
            'message' => $message
        ];

        $userPushMessage = $this->textProcessor->formatPushMessage($message);

        // Simple email job dispatch
        $this->queueManager->dispatch(
            SimpleMailJob::class,
            [
                $mail['subject'],
                $userEmail,
                $mail['message']
            ],
            'emails'
        );

        // Push notification job dispatch
        $this->queueManager->dispatch(
            PushNotificationJob::class,
            [
                "Single $userRole",
                $userId,
                'Task Approved',
                $userPushMessage,
                ['url' => "{$this->baseUrl}/login", 'type' => 'task']
            ],
            'push'
        );

        return $this->response->success('Task approved successfully');
    }

    // =========================================
    // GET TASK ATTEMPTS
    // =========================================
    public function getAttempt(
        int $taskId, 
        int $page
    ): array { 

        $attempts = $this->taskModel->getAttempt($taskId, $page);
        if ($attempts === false) {
            return $this->response->fail('Failed to get attempts', 500);
        }
        
        return $this->response->success('Attempts fetched successfully', $attempts);
    }

    // =========================================
    // GET TASK DESCRIPTION
    // =========================================
    public function getDescription(
        int $taskId
    ): array { 

        $description = $this->taskModel->getDescription($taskId);
        if ($description === false) {
            return $this->response->fail('Failed to get description', 500);
        }
        
        return $this->response->success('Description fetched successfully', ['description' => $description]);
    }

    // =========================================
    // LOG ERROR MESSAGES
    // =========================================
    private function logError(
        string $message
    ): void {
        
        $logFile   = dirname(__DIR__, 2) . '/storage/logs/php-error.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry     = "[{$timestamp}] {$message}\n";
        
        error_log($entry, 3, $logFile);
    }
}
