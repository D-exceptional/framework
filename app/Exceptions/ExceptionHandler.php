<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Request;
use App\Http\Response;
use App\Http\ResponseEmitter;
use Throwable;

class ExceptionHandler
{
    public function __construct(
        protected Request $request,
        protected Response $response,
        protected ResponseEmitter $emitter,
        protected string $logFile
    ) {
        $this->initialize();
    }

    /**
     * Register PHP error, exception and shutdown handlers.
     */
    protected function initialize(): void
    {
        $directory = dirname($this->logFile);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        // Enable error reporting and logging based on the application configuration.
        ini_set('display_errors', config('app.debug') ? '1' : '0');
        ini_set('display_startup_errors', config('app.debug') ? '1' : '0');

        error_reporting(E_ALL);

        ini_set('log_errors', '1');
        ini_set('error_log', $this->logFile);

        set_error_handler(
            [$this, 'handleError']
        );

        set_exception_handler(
            [$this, 'handleException']
        );

        register_shutdown_function(
            [$this, 'handleShutdown']
        );
    }

    /**
     * Handle PHP errors.
     */
    public function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool {

        /*
         * Ignore errors that are not included in the
         * current error_reporting configuration.
         */
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $this->reportError(
            $errno,
            $errstr,
            $errfile,
            $errline
        );

        /*
         * Returning false allows PHP's normal error
         * handling to continue.
         */
        return false;
    }

    /**
     * Handle uncaught exceptions.
     */
    public function handleException(
        Throwable $exception
    ): void {

        $this->report($exception);

        $response = $this->render(
            $exception
        );

        $this->emitter->emit(
            $response
        );
    }

    /**
     * Handle fatal errors during shutdown.
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if (!$error) {
            return;
        }

        $fatalErrors = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING,
        ];

        if (!in_array($error['type'], $fatalErrors, true)) {
            return;
        }

        $this->reportError(
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );
    }

    /**
     * Report an exception.
     */
    public function report(
        Throwable $exception
    ): void {

        $this->writeLog(
            sprintf(
                "UNCAUGHT EXCEPTION: %s in %s on line %d\n%s",
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString()
            )
        );
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render(
        Throwable $exception
    ): Response {

        /*
         * Middleware may explicitly request
         * a redirect response.
         */
        if (
            $exception instanceof MiddlewareException
            && $exception->action === 'redirect'
            && $exception->redirect
        ) {
            return $this->response->redirect(
                $exception->redirect,
                $exception->status
            );
        }

        /*
         * JSON / API response.
         */
        if ($this->request->expectsJson()) {
            return $this->renderJson(
                $exception
            );
        }

        /*
         * Web / HTML response. 
         * Change to renderView() if you want to use the error view templates instead of the custom error page.
         */
        return $this->renderView(
            $exception
        );
    }

    /**
     * Render JSON exception response.
     */
    protected function renderJson(
        Throwable $exception
    ): Response {

        $status = $this->getStatusCode(
            $exception
        );

        $data = [
            'ok' => false,
            'message' => $this->getMessage(
                $exception
            ),
        ];

        /*
         * Validation errors need to be returned
         * to the client.
         */
        if ($exception instanceof ValidationException) {
            $data['errors'] = $exception->errors;
        }

        /*
         * Include debugging information only
         * when application debugging is enabled.
         */
        if (config('app.debug')) {
            $data['exception'] = [
                'class' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        return $this->response->json(
            $data,
            $status
        );
    }

    /**
     * Render error view response.
     */
    protected function renderView(
        Throwable $exception
    ): Response {

        $status = $this->getStatusCode(
            $exception
        );

        $message = $this->getMessage(
            $exception
        );

        $view = $this->getErrorView(
            $status
        );

        return $this->response->view(
            $view,
            [
                'message' => $message,
                'exception' => $exception,
                'status' => $status,
            ],
            $status
        );
    }

    /**
     * Render a custom error page.
     */
    protected function renderHtml(
        Throwable $exception
    ): Response {

        $status = $this->getStatusCode(
            $exception
        );

        $message = $this->getMessage(
            $exception
        );

        /*
         * Default error page. 
         * Could be changed to 500.php or any other error page as needed.
         */
        $errorFile = ROOT_PATH . '/resources/errors/template.php'; 

        if (!is_file($errorFile)) {

            return $this->response->html(
                '<h1>' . $status . '</h1><p>' .
                htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    'UTF-8'
                ) .
                '</p>',
                $status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Error Page Data
        |--------------------------------------------------------------------------
        */

        $data = [
            'message' => $message,
            'exception' => $exception,
            'status' => $status,
        ];

        /*
        |--------------------------------------------------------------------------
        | Render Error Template
        |--------------------------------------------------------------------------
        */

        ob_start();

        extract(
            $data,
            EXTR_SKIP
        );

        require $errorFile;

        $html = ob_get_clean();

        /*
        |--------------------------------------------------------------------------
        | Return HTML Response
        |--------------------------------------------------------------------------
        */

        return $this->response->html(
            $html,
            $status
        );
    }

    /**
     * Determine the HTTP status code.
     */
    protected function getStatusCode(
        Throwable $exception
    ): int {

        if ($exception instanceof ValidationException) {
            return $exception->status;
        }

        if ($exception instanceof MiddlewareException) {
            return $exception->status;
        }

        if ($exception instanceof RouteNotFoundException) {
            return 404;
        }

        return 500;
    }

    /**
     * Determine the client-facing exception message.
     */
    protected function getMessage(
        Throwable $exception
    ): string {

        /*
         * Development environment:
         * expose the actual exception message.
         */
        if (config('app.debug')) {
            return $exception->getMessage();
        }

        /*
         * Known application exceptions are safe
         * to expose to the client.
         */
        if (
            $exception instanceof ValidationException
            || $exception instanceof MiddlewareException
            || $exception instanceof RouteNotFoundException
        ) {
            return $exception->getMessage();
        }

        /*
         * Hide unexpected exception details
         * in production.
         */
        return 'Internal Server Error';
    }

    /**
     * Determine the HTML error view.
     */
    protected function getErrorView(
        int $status
    ): string {

        /*
         * Error views are located in the /resources/views/errors or /errors directory.
         * Create the /errors directory and add each of the following files:
         * 401.php, 403.php, 404.php, 422.php, 500.php
         * Style the error pages as desired. If a view file is not found, a default
         * error page will be rendered instead.
         */

        return match ($status) {
            401 => 'errors.401',
            403 => 'errors.403',
            404 => 'errors.404',
            422 => 'errors.422',
            default => 'errors.500',
        };
    }

    /**
     * Report a PHP error.
     */
    protected function reportError(
        int $errno,
        string $message,
        string $file,
        int $line
    ): void {

        $this->writeLog(
            sprintf(
                "PHP ERROR [%d]: %s in %s on line %d",
                $errno,
                $message,
                $file,
                $line
            )
        );
    }

    /**
     * Write a message to the exception log.
     */
    protected function writeLog(
        string $message
    ): void {

        $time = date('Y-m-d H:i:s');

        $formatted = sprintf(
            "[%s] %s\n",
            $time,
            $message
        );

        error_log(
            $formatted,
            3,
            $this->logFile
        );
    }
}