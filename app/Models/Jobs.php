<?php

declare(strict_types=1);

namespace App\Models;

class Jobs extends Model
{
    protected string $table = 'jobs_log';

    // =========================================
    // CREATE JOB LOG
    // =========================================
    public function createJobLog(
        string $queue,
        string $jobClass,
        string $payload,
        int $maxAttempts,
        int $delay
    ): int {

        return $this->query()
            ->insertGetId([
                'queue'        => $queue,
                'job_class'    => $jobClass,
                'payload'      => $payload,
                'status'       => 'pending',
                'attempts'     => 0,
                'max_attempts' => $maxAttempts,
                'available_at' => date('Y-m-d H:i:s', time() + $delay)
            ]);
    }

    // =========================================
    // UPDATE PROCESSING JOB LOG
    // =========================================
    public function updateProcessingJobLog(
        int $jobId
    ): bool {

        return $this->query()
            ->where('id', '=', $jobId)
            ->update(['status' => 'processing']);
    }

    // =========================================
    // UPDATE SUCCESS JOB LOG
    // =========================================
    public function updateSuccessJobLog(
        int $jobId
    ): bool {

        return $this->query()
            ->where('id', '=', $jobId)
            ->update([
                'status'         => 'completed', 
                'execution_time' => date('Y-m-d H:i:s'), 
                'processed_at'   => date('Y-m-d H:i:s')
            ]);
    }

    // =========================================
    // UPDATE FAILED JOB LOG
    // =========================================
    public function updateFailedJobLog(
        int $jobId,
        string $errorMessage,
        int $attempts
    ): bool {

        return $this->query()
            ->where('id', '=', $jobId)
            ->update([
                'status'        => 'failed', 
                'error_message' => $errorMessage, 
                'failed_at'     => date('Y-m-d H:i:s'), 
                'attempts'      => $attempts
            ]);
    }
    
    // =========================================
    // DELETE JOB LOG
    // =========================================
    public function deleteJobLog(
        int $jobId
    ): bool {

        return $this->query()
            ->where('id', '=', $jobId)
            ->delete();
    }

    // =========================================
    // FIND ONE JOB LOG
    // =========================================
    public function findOne(
        int $jobId
    ): ?array {

        return $this->query()
            ->where('id', '=', $jobId)
            ->first();
    }

    // =========================================
    // FIND JOBS BY STATUS
    // =========================================
    public function findByStatus(
        ?string $status = null,
        int $page = 1,
        int $perPage = 20
    ): array {

        return $this->query()
            ->when(
                $status
                && in_array($status, ['pending', 'processing','completed','failed']),

                fn($query) =>
                    $query->where('status', '=', $status)
            )
            ->orderBy('id', 'DESC')
            ->paginate($page, $perPage)
            ->get();
    }

    // =========================================
    // COUNT JOBS BY STATUS
    // =========================================
    public function countJobs(
        ?string $status = null
    ): int {

        return $this->query()
            ->when(
                $status
                && !is_null($status),

                fn($query) =>
                    $query->where('status', '=', $status)
            )
            ->count();
    }

    // =========================================
    // GET JOB DASHBOARD STATS
    // =========================================
    public function getJobStats(): array 
    {

        return [
            'pending'    => $this->countJobs('pending'),

            'processing' => $this->countJobs('processing'),

            'completed'  => $this->countJobs('completed'),

            'failed'     => $this->countJobs('failed'),
        ];
    }
}