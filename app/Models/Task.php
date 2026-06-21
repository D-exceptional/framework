<?php

namespace App\Models;

class Task extends Model
{
    protected string $table = 'paid_tasks';

    // =========================================
    // CREATE TASK
    // =========================================
    public function createTask(
        string $name, 
        string $description, 
        int $reward, 
        string $start, 
        string $end
    ): bool {

        return $this->query()
            ->insert([
                'task_name'        => $name,
                'task_description' => $description,
                'task_reward'      => $reward,
                'begin_date'       => $start,
                'end_date'         => $end,
            ]);
    }

    // =========================================
    // UPDATE TASK DETAILS
    // =========================================
    public function updateDetails(
        int $taskId, 
        string $name, 
        string $description, 
        int $reward, 
        string $start, 
        string $end, 
        string $status
    ): bool {

        return $this->query()
            ->where('task_id', '=', $taskId)
            ->update([
                'task_name'        => $name,
                'task_description' => $description,
                'task_reward'      => $reward,
                'begin_date'       => $start,
                'end_date'         => $end,
                'task_status'      => $status
            ]);
    }

    // =========================================
    // UPDATE STATUS (SINGLE)
    // =========================================
    public function updateStatus(
        string $status, 
        int $taskId
    ): bool {

        return $this->query()
            ->where('task_id', '=', $taskId)
            ->update(['task_status' => $status]);
    }

    // =========================================
    // UPDATE STATUS (MANY)
    // =========================================
    public function updateAll(
        string $status, 
        string $date
    ): bool {

        return $this->query()
            ->where('end_date', '<', $date)
            ->update(['task_status' => $status]);
    }

    // =========================================
    // DELETE TASK
    // =========================================
    public function deleteTask(
        int $taskId
    ): bool {

        return $this->query()
            ->where('task_id', '=', $taskId)
            ->delete();
    }

    // =========================================
    // GET TASK (SINGLE)
    // =========================================
    public function findOne(
        int $taskId
    ): array {

        return $this->query()
            ->where('task_id', '=', $taskId)
            ->first();
    }

    // =========================================
    // GET TASK DESCRIPTION
    // =========================================
    public function getDescription(
        int $taskId
    ): ?string {

        $result = $this->query()
            ->select(['task_description'])
            ->where('task_id', '=', $taskId)
            ->first();

        return $result
            ? $result['task_description']
            : null;
    }

    // =========================================
    // FIND TASKS BY STATUS
    // =========================================
    public function findByStatus(
        ?string $status = null,
        int $page = 1,
        int $limit = 20
    ): array {

        $today = date('Y-m-d H:i:s');

        return $this->query()
            ->when(
                $status &&
                in_array($status, ['active', 'Active']),
                fn ($query) =>
                    $query->where('end_date', '>', $today)
            )
            ->when(
                $status &&
                in_array($status, ['expired', 'Expired']),
                fn ($query) =>
                    $query->where('end_date', '<', $today)
            )
            ->paginate($page, $limit)
            ->get();
    }

    // =========================================
    // ATTEMPT TASK
    // =========================================
    public function attemptTask(
        string $link, 
        int $taskId, 
        int $userId
    ): bool {

        return $this->executeQuery(
            "INSERT INTO paid_task_participants (task_link, task_id, user_id) VALUES (?, ?, ?)",
            [$link, $taskId, $userId]
        );
    }

    // =========================================
    // FINALIZE TASK ATTEMPT
    // =========================================
    public function finalizeAttempt(
        int $taskId, 
        int $userId, 
        string $status
    ): bool {

        return $this->executeQuery(
            "UPDATE paid_task_participants SET task_status = ? WHERE task_id = ? AND user_id = ?",
            [$status, $taskId, $userId]
        );
    }

    // =========================================
    // COUNT TASKS
    // =========================================
    public function countTasks(
        ?int $userId = null, 
        ?string $status = null
    ): int {

        return $this->query()
            ->when(
                $userId &&
                !is_null($userId),

                fn($query) =>
                    $query->where('user_id', '=', $userId)
            )
            ->when(
                $status &&
                !is_null($status),

                fn($query) =>
                    $query->where('task_status', '=', $status)
            )
            ->count();
    }

    // =========================================
    // GET STATS
    // =========================================
    public function getTaskStats(): ?array
    {
        return [
            'active'  => $this->countTasks(null, 'Active'),
            'expired' => $this->countTasks(null, 'Expired'),
        ];
    }

    // =========================================
    // GET TASK ATTEMPTS
    // =========================================
    public function getAttempt(
        ?int $taskId = null, 
        int $page = 1, 
        int $limit = 20
    ): array {

        $offset = ($page - 1) * $limit;

        $sql = "SELECT 
                ptp.created_at,
                ptp.task_link,
                ptp.task_status,

                u.user_id,
                u.fullname,

                pt.task_name,
                pt.task_reward
            
            FROM paid_task_participants ptp
            INNER JOIN users u ON ptp.user_id = u.user_id
            LEFT JOIN {$this->table} pt ON ptp.task_id = pt.task_id
            WHERE ptp.task_id = ?
            ORDER BY ptp.participant_id DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $attempts = $this->queryAll(
            $sql,
            [$taskId]
        );

        $total = $this->fetchColumn(
            "SELECT COUNT(*) FROM paid_task_participants WHERE task_id = ?", 
            [$taskId]
        );

        return $this->format($attempts, $total, $page, $limit);
    }

    // =========================================
    // FETCH TASKS
    // =========================================
    public function fetchTasks(
        ?int $userId = null, 
        int $page = 1, 
        int $limit = 20
    ): array {

        $offset = ($page - 1) * $limit;

        $sql = "SELECT 
                pt.task_id,
                pt.task_name,
                pt.task_reward,
                pt.begin_date,
                pt.end_date,
                pt.task_status AS task_global_status,

                ptp.participant_id,
                ptp.task_link,
                ptp.task_status AS user_task_status,
                ptp.created_at AS participated_at,

                CASE 
                    WHEN ptp.participant_id IS NULL THEN 'Not Done'
                    ELSE ptp.task_status
                END AS participation_status

            FROM paid_tasks pt
            LEFT JOIN paid_task_participants ptp
                ON pt.task_id = ptp.task_id
                AND ptp.user_id = ?

            ORDER BY pt.task_id DESC 
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->queryAll(
            $sql,
            [$userId]
        );
    }

    // =========================================
    // RESULT FORMATTER (HELPER FUNCTION)
    // =========================================
    private function format(
        array $data, 
        int $total, 
        int $page, 
        int $limit
    ): array {

        return [
            'attempts'    => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $limit,
            'total_pages' => ceil($total / $limit),
        ];
    }
}
