<?php

declare(strict_types=1);

namespace App\Listeners;

class Listener
{
    public function __construct() {}

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