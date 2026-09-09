<?php

declare(strict_types=1);

namespace App\Queue;

use App\Redis\RedisManager;
use App\Redis\RedisQueue;
use App\Models\Jobs;

class Queue extends RedisQueue
{
    public function __construct(
        RedisManager $redis, 
        protected Jobs $jobModel
    ) {
        parent::__construct(
            $redis->queue()
        );
    }

    // =========================================
    // DISPATCH JOB TO QUEUE WORKER
    // =========================================
    public function dispatch(
        string $jobClass,
        array $data      = [],
        string $queue    = 'default',
        int $delay       = 0,
        int $maxAttempts = 3,
        int $timeout     = 30
    ): void {

        $encodedData = json_encode($data);

        $jobId = $this->jobModel->createJobLog($queue, $jobClass, $encodedData, $maxAttempts, $delay);

        $payload = json_encode([
            'job_id'       => $jobId,   
            'class'        => $jobClass,
            'data'         => $data,
            'attempts'     => 0,
            'max_attempts' => $maxAttempts,
            'timeout'      => $timeout,
            'available_at' => time() + $delay
        ]);

        $this->push($queue, $payload);
    }
}