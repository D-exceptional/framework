<?php

declare(strict_types=1);

namespace App\Queue;

use App\Core\Container;
use App\Redis\Redis;
use App\Redis\RedisQueue;
use App\Models\Jobs;

class QueueWorker extends RedisQueue
{
    public function __construct(
        protected Container $container,
        Redis $redis,
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

            // -----------------------------------------
            // NO JOB AVAILABLE
            // -----------------------------------------
            if (!$jobData) {

                // Prevent the worker from continuously
                // hammering Redis when the queue is empty.
                sleep(1);

                continue;
            }

            // Always initialize this before the try block.
            // This prevents the catch block from referencing
            // an undefined variable if payload decoding fails.
            $payload = [];

            try {

                // -----------------------------------------
                // DECODE JOB PAYLOAD
                // -----------------------------------------
                $payload = json_decode(
                    $jobData[1],
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                if (!is_array($payload)) {
                    throw new \RuntimeException(
                        'Invalid job payload'
                    );
                }

                // -----------------------------------------
                // DELAY HANDLING
                // -----------------------------------------
                if (
                    isset($payload['available_at'])
                    && $payload['available_at'] > time()
                ) {

                    $this->push(
                        $queue,
                        json_encode($payload, JSON_THROW_ON_ERROR)
                    );

                    sleep(1);

                    continue;
                }

                // -----------------------------------------
                // VALIDATE REQUIRED PAYLOAD DATA
                // -----------------------------------------
                $required = [
                    'job_id',
                    'class',
                    'data',
                    'attempts',
                    'max_attempts',
                    'timeout',
                    'available_at'
                ];

                foreach ($required as $field) {

                    if (!array_key_exists($field, $payload)) {

                        throw new \RuntimeException(
                            "Invalid job payload: missing {$field}"
                        );
                    }
                }

                $class = $payload['class'];
                $data  = $payload['data'];

                // -----------------------------------------
                // VERIFY JOB CLASS
                // -----------------------------------------
                if (!class_exists($class)) {

                    throw new \RuntimeException(
                        "Job not found: {$class}"
                    );
                }

                // -----------------------------------------
                // RESOLVE JOB THROUGH CONTAINER
                // -----------------------------------------
                $job = $this->container->get($class);

                // -----------------------------------------
                // INJECT RUNTIME DATA
                // -----------------------------------------
                $job->setPayload($data);

                // -----------------------------------------
                // UPDATE PROCESSING STATUS
                // -----------------------------------------
                $this->jobModel->updateProcessingJobLog(
                    $payload['job_id']
                );

                // -----------------------------------------
                // START EXECUTION TIMER
                // -----------------------------------------
                $start = microtime(true);

                // -----------------------------------------
                // EXECUTE JOB
                // -----------------------------------------
                $job->handle();

                // -----------------------------------------
                // TIMEOUT CHECK
                // -----------------------------------------
                $duration = microtime(true) - $start;

                if ($duration > $payload['timeout']) {

                    throw new \RuntimeException(
                        sprintf(
                            'Job timeout exceeded: %.2f seconds (limit: %d seconds)',
                            $duration,
                            $payload['timeout']
                        )
                    );
                }

                // -----------------------------------------
                // UPDATE SUCCESS STATUS
                // -----------------------------------------
                $this->jobModel->updateSuccessJobLog(
                    $payload['job_id']
                );

                echo "Job processed successfully\n";

            } catch (\Throwable $e) {

                // -----------------------------------------
                // DETERMINE ATTEMPT NUMBER
                // -----------------------------------------
                $attempts = (int) (
                    $payload['attempts'] ?? 0
                );

                $maxAttempts = (int) (
                    $payload['max_attempts'] ?? 1
                );

                $attempts++;

                // Keep the updated attempt count
                // inside the payload for retries.
                $payload['attempts'] = $attempts;

                // -----------------------------------------
                // RETRY LOGIC
                // -----------------------------------------
                if (
                    $attempts < $maxAttempts
                    && isset($payload['class'])
                ) {

                    $retryDelay = $this->calculateRetryDelay(
                        $attempts
                    );

                    $payload['available_at'] =
                        time() + $retryDelay;

                    echo sprintf(
                        "Job failed. Retrying attempt %d/%d in %d seconds: %s\n",
                        $attempts,
                        $maxAttempts,
                        $retryDelay,
                        $e->getMessage()
                    );

                    $this->push(
                        $queue,
                        json_encode(
                            $payload,
                            JSON_THROW_ON_ERROR
                        )
                    );

                    continue;
                }

                // -----------------------------------------
                // PERMANENT FAILURE
                // -----------------------------------------
                $this->handlePermanentFailure(
                    $payload,
                    $e
                );
            }
        }
    }

    // =========================================
    // CALCULATE RETRY DELAY
    // =========================================
    protected function calculateRetryDelay(
        int $attempt
    ): int {

        /*
         * Exponential backoff:
         *
         * Attempt 1 → 5 seconds
         * Attempt 2 → 10 seconds
         * Attempt 3 → 20 seconds
         * Attempt 4 → 40 seconds
         * ...
         *
         * Maximum delay is capped at 300 seconds.
         */

        $baseDelay = 5;

        $maxDelay = 300;

        $delay = $baseDelay * (2 ** ($attempt - 1));

        return min(
            $delay,
            $maxDelay
        );
    }

    // =========================================
    // HANDLE PERMANENTLY FAILED JOB
    // =========================================
    protected function handlePermanentFailure(
        array $payload,
        \Throwable $exception
    ): void {

        $error = $exception->getMessage();

        // -----------------------------------------
        // FAILED JOB STORAGE
        // -----------------------------------------
        $this->push(
            'failed',
            json_encode(
                [
                    'payload'   => $payload,
                    'error'     => $error,
                    'failed_at' => time()
                ],
                JSON_THROW_ON_ERROR
            )
        );

        // -----------------------------------------
        // UPDATE FAILED JOB STATUS
        // -----------------------------------------
        if (isset($payload['job_id'])) {

            $this->jobModel->updateFailedJobLog(
                $payload['job_id'],
                $error,
                (int) ($payload['attempts'] ?? 0)
            );
        }

        echo "Job permanently failed: {$error}\n";
    }
}