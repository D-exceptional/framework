<?php

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\CategoryService;

class CategoryController
{
    protected Response $response;
    protected CategoryService $service;

    public function __construct(Response $response, CategoryService $service)
    {
        $this->response = $response;
        $this->service  = $service;
    }

    // =========================================
    // GET ALL CATEGORIES
    // =========================================
    public function all(Request $request)
    {
        $result = $this->service->all();
        return $this->response->flash($result);
    }

    // =========================================
    // CREATE CATEGORY
    // =========================================
    public function create(Request $request)
    {
        $result = $this->service->create(
            $request->input('category') 
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE CATEGORY
    // =========================================
    public function update(Request $request)
    {
        $result = $this->service->update(
            $request->input('name'),
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // DELETE CATEGORY
    // =========================================
    public function delete(Request $request)
    { 
        $result = $this->service->delete(
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // COUNT CATEGORIES
    // =========================================
    public function count(Request $request)
    { 
        $result = $this->service->count();
        return $this->response->flash($result);
    }
}
