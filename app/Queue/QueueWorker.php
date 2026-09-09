<?php

declare(strict_types=1);

namespace App\Queue;

use App\Core\Container;
use App\Redis\RedisManager;
use App\Redis\RedisQueue;
use App\Models\Jobs;

class QueueWorker extends RedisQueue
{
    public function __construct(
        protected Container $container,
        RedisManager $redis, 
        protected Jobs $jobModel
    ) {
        parent::__construct(
            $redis->queue()
        );  
    }

    // =========================================
    // RUN JOBS IN QUEUE
    // =========================================
    public function run(
        string $queue = 'default'
    ): void {   

        echo "Worker running on: {$queue}\n";

        while (true) {

            $jobData = $this->pop($queue); 

            if (!$jobData) {
                continue;
            }

            try {

                $payload = json_decode($jobData[1], true);

                // -----------------------------------
                // DELAY HANDLING
                // -----------------------------------
                if ($payload['available_at'] > time()) {

                    $this->push(
                        $queue,
                        json_encode($payload)
                    );

                    sleep(1); // Wait 1 second before checking the queue again

                    continue;
                }

                $class = $payload['class'];
                $data  = $payload['data'];

                if (!class_exists($class)) {
                    throw new \Exception("Job not found: {$class}");
                }

                $job = $this->container->get($class);

                // INJECT RUNTIME DATA AFTER CONSTRUCTION
                $job->setPayload($data);

                // UPDATE PROCESSING STATUS
                $this->jobModel->updateProcessingJobLog($payload['job_id']);

                // -----------------------------------
                // TIMEOUT CONTROL
                // -----------------------------------
                $start = time();

                $job->handle();

                if ((time() - $start) > $payload['timeout']) {
                    throw new \Exception("Job timeout exceeded");
                }

                // UPDATE SUCCESS STATUS
                $this->jobModel->updateSuccessJobLog($payload['job_id']);

                echo "Job processed\n";

            } catch (\Throwable $e) {

                $payload['attempts']++;

                // -----------------------------------
                // RETRY LOGIC
                // -----------------------------------
                if ($payload['attempts'] < $payload['max_attempts']) {

                    echo "Retrying job... attempt {$payload['attempts']}\n";

                    $this->push(
                        $queue,
                        json_encode($payload)
                    );

                } else {

                    // -----------------------------------
                    // FAILED JOB STORAGE
                    // -----------------------------------
                    $this->push(
                        "failed",
                        json_encode([
                            'payload'   => $payload,
                            'error'     => $e->getMessage(),
                            'failed_at' => time()
                        ])
                    );

                    // UPDATE FAILED STATUS
                    $this->jobModel->updateFailedJobLog($payload['job_id'], $e->getMessage(), $payload['attempts']);

                    echo "Job permanently failed: {$e->getMessage()}\n";
                }
            }
        }
    }
}

