<?php

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\BlogService;

class BlogController 
{
    protected Response $response;
    protected BlogService $service;

    public function __construct(Response $response, BlogService $service)
    {
        $this->response = $response;
        $this->service  = $service;
    }

    // =========================================
    // CREATE BLOG
    // =========================================
    public function createBlog(Request $request)
    {
        $userId = $request->user()['id'];
        $result = $this->service->createBlog(
            $request->input('banner'), 
            $request->input('title'), 
            $request->input('category'), 
            $request->input('article'), 
            $userId
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE BLOG DETAILS
    // =========================================
    public function updateDetails(Request $request)
    {
        $result = $this->service->updateDetails(
            $request->input('title'), 
            $request->input('article'),  
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE BLOG BANNER
    // =========================================
    public function updateBanner(Request $request)
    {
        $result = $this->service->updateBanner(
            (int) $request->input('id'), 
            $request->input('url')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE BLOG STATUS
    // =========================================
    public function updateStatus(Request $request)
    {
        $result = $this->service->updateStatus(
            $request->input('status'),  
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE BLOG COUNTERS (LIKES, VIEWS)
    // =========================================
    public function updateCounter(Request $request)
    { 
        $result = $this->service->updateCounter(
            (int) $request->input('id'), 
            $request->input('type')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // DELETE BLOG
    // =========================================
    public function deleteBlog(Request $request)
    {
        $result = $this->service->deleteBlog(
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET SINGLE BLOG DATA
    // =========================================
    public function findOne(Request $request)
    { 
        $result = $this->service->findOne(
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET BLOGS BY STATUS
    // =========================================
    public function findByStatus(Request $request)
    { 
        $result = $this->service->findByStatus(
            $request->input('status'), 
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET BLOGS BY AUTHORS (USERS)
    // =========================================
    public function findByUser(Request $request)
    { 
        $result = $this->service->findByUser(
            (int) $request->input('id'), 
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }
}
