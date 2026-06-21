<?php
namespace App\Http\Controllers;

use App\Http\Response;
use App\Services\TestService;

class TestController
{
    protected Response $response;
    protected TestService $service;

    public function __construct(Response $response, TestService $service)
    {
        $this->response = $response;
        $this->service  = $service;
    }

    // =========================================
    // CHECK API WORKING STATUS
    // =========================================
    public function ping() 
    {
        $result = $this->service->ping();
        return $this->response->flash($result);
    }

    // =========================================
    // TEST REDIS CONNECTION
    // =========================================
    public function beep()
    {
        $result = $this->service->beep();
        return $this->response->flash($result);
    }
}
