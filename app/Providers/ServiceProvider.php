<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container;

abstract class ServiceProvider
{
    /**
     * Register services into the container.
     */
    public function register(): void
    {}

    /**
     * Boot services after all providers
     * have been registered.
     */
    public function boot(): void
    {}

    // =========================================
    // WRITE MEDIA LOG
    // =========================================
    protected function log(
        mixed $data
    ): void {

        $timestamp = date('Y-m-d H:i:s');

        $message = is_array($data)
            ? json_encode($data, JSON_PRETTY_PRINT)
            : (string) $data;

        $logFile =
            dirname(__DIR__, 2) .
            '/storage/logs/provider.log';

        $result = file_put_contents(
            $logFile,
            "[{$timestamp}] {$message}" . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($result === false) {
            throw new \RuntimeException(
                "Unable to write service provider log: {$logFile}"
            );
        }
    }
}