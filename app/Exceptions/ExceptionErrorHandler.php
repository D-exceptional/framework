<?php
namespace App\Exceptions;

class ExceptionErrorHandler
{
    private string $logFile;

    public function __construct(string $logPath)
    {
        $this->logFile = $logPath;
        $this->initialize();
    }

    // =========================================
    // INITIALIZE ERROR HANDLER CLASS
    // =========================================
    private function initialize(): void
    {
        // Ensure file and directory exist
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Register handlers
        ini_set('log_errors', '1');
        ini_set('error_log', $this->logFile);

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    // =========================================
    // HANDLE ERROR
    // =========================================
    public function handleError(
        int $errno, 
        string $errstr, 
        string $errfile, 
        int $errline
    ): bool {

        $this->writeLog("PHP ERROR [{$errno}]: {$errstr} in {$errfile} on line {$errline}");
        return true;
    }

    // =========================================
    // HANDLE EXCEPTION
    // =========================================
    public function handleException(\Throwable $exception): void
    {
        $this->writeLog("UNCAUGHT EXCEPTION: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    }

    // =========================================
    // HANDLE SHUTDOWN
    // =========================================
    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->writeLog("FATAL ERROR: {$error['message']} in {$error['file']} on line {$error['line']}");
        }
    }

    // =========================================
    // WRITE TO ERROR LOG FILE
    // =========================================
    private function writeLog(
        string $message
    ): void {

        $time = date('Y-m-d H:i:s');
        $formatted = "[{$time}] {$message}\n";
        
        error_log($formatted, 3, $this->logFile);
    }
}
