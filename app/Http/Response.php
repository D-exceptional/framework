<?php

namespace App\Http;

class Response
{
    // =========================================
    // CONTROLLER SUCCESS RESPONSE
    // =========================================
    public function succeed(
        string $message = 'Success', 
        $data = null, 
        int $status = 200
    ): void {

        $this->format(true, $status, $message, $data, null);
    }

    // =========================================
    // CONTROLLER AND ROUTER ERROR RESPONSE
    // =========================================
    public function error(
        string $message = 'An error occurred', 
        int $status = 400, 
        $error = null
    ): void {

        $this->format(false, $status, $message, null, $error);
    }

    // =========================================
    // FORMAT CONTROLLER RESPONSE CLEANLY
    // =========================================
    protected function format(
        bool $ok, 
        int $status, 
        string $message, 
        mixed $data, 
        mixed $error
    ): void {

        http_response_code($status);
        header('Content-Type: application/json'); 

        echo json_encode([
            'ok'      => $ok,
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
            'error'   => $error
        ]);
    }

    // =========================================
    // SERVICE SUCCESS RESPONSE
    // =========================================
    public function success(
        ?string $message = null,
        array $data = [],
        int $status = 200
    ): array {
        return [
            'ok'      => true,
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
            'error'   => null
        ];
    }

    // =========================================
    // SERVICE ERROR RESPONSE
    // =========================================
    public function fail(
        ?string $message = null,
        int $status = 400,
        mixed $error = null
    ): array {
        return [
            'ok'      => false,
            'status'  => $status,
            'message' => $message,
            'data'    => null,
            'error'   => $error
        ];
    }

    // =========================================
    // FORMAT OUTGOING RESPONSE CLEANLY
    // =========================================
    public function flash(
        array $result
    ): never {

        http_response_code($result['status']);     
        header('Content-Type: application/json'); 

        echo json_encode([
            'ok'      => $result['ok'],
            'status'  => $result['status'],
            'message' => $result['message'],
            'data'    => $result['data'],
            'error'   => $result['error']
        ]);

        exit;
    }
}
