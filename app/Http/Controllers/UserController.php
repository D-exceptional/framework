<?php

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\UserService;

class UserController 
{
    protected Response $response;
    protected UserService $service;

    public function __construct(Response $response, UserService $service)
    {
        $this->response = $response;
        $this->service  = $service;
    }

    // =========================================
    // REGISTER USER
    // =========================================
    public function register(Request $request)
    {
        $result = $this->service->register(
            $request->input('avatar'),
            $request->input('firstname'),
            $request->input('lastname'),
            $request->input('email'),
            $request->input('contact'),
            $request->input('country'),
            $request->input('password'),
            $request->input('membership'),
            $request->input('state'),
            $request->input('lga'),
            $request->input('receipt'),
            (int) $request->input('amount'),
            $request->input('narration'),
            (int) $request->input('id'),
            (int) $request->input('facilitator'),
            $request->input('currency'),
            $request->input('code')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // LOGIN USER
    // =========================================
    public function login(Request $request) 
    {
        $result = $this->service->login(
            $request->input('email'),
            $request->input('password')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET OTP
    // =========================================
    public function otp(Request $request)
    {
        $result = $this->service->sendOtp(
            $request->input('email')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // RESET PASSWORD
    // =========================================
    public function reset(Request $request)
    {
        $result = $this->service->reset(
            (int) $request->input('otp'),
            $request->input('password'),
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE BIO
    // =========================================
    public function update(Request $request)
    {
        $userId = $request->user()['id'];
        $result = $this->service->update(
            $request->input('bio'),
            $userId
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE SOCIAL HANDLES
    // =========================================
    public function social(Request $request)
    {
        $userId = $request->user()['id'];
        $result = $this->service->social(
            $request->input('facebook'),
            $request->input('instagram'),
            $request->input('tiktok'),
            $request->input('twitter'),
            $userId
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE PROFILE PICTURE
    // =========================================
    public function profile(Request $request)
    {
        $userId = $request->user()['id'];
        $result = $this->service->profile(
            $request->input('avatar'),
            $userId
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE PASSWORD
    // =========================================
    public function password(Request $request)
    {
        $userId = $request->user()['id'];
        $result = $this->service->password(
            $request->input('password'),
            $request->input('newpassword'),
            $userId
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE STATUS
    // =========================================
    public function status(Request $request)
    {
        $result = $this->service->status(
            $request->input('status'),
            (int) $request->input('id'),
        );
        return $this->response->flash($result);
    }

    // =========================================
    // COUNT USERS
    // =========================================
    public function count(Request $request)
    { 
        $result = $this->service->count();
        return $this->response->flash($result);
    }

    // =========================================
    // LOGOUT USER
    // =========================================
    public function logout(Request $request)
    {
        $role   = $request->user()['role'] ?? null;
        $result = $this->service->logout($role);
        return $this->response->flash($result);
    }

    // =========================================
    // SEND CONTACT MESSAGE
    // =========================================
    public function contact(Request $request)                             
    {
        $result = $this->service->contact(
            $request->input('name'),
            $request->input('email'),
            $request->input('contact'),
            $request->input('country'),
            $request->input('subject'),
            $request->input('message'),
            $request->input('code')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // SUBSCRIBE FOR PUSH NOTIFICATION
    // =========================================
    public function subscribe(Request $request)
    {
        // Get details
        $user = $request->user();
        $userId = $user['id'];
        $userType = ucfirst($user['role']);

        $result = $this->service->subscribe(
            $request->input('token'), 
            $request->input('device_id'),
            $userId, 
            $userType
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UNSUBSCRIBE FOR PUSH NOTIFICATION
    // =========================================
    public function unsubscribe(Request $request)
    {
        $result = $this->service->unsubscribe(
            $request->input('token'), 
            $request->input('device_id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // FETCH USERS BY ROLE
    // =========================================
    public function fetch(Request $request)
    { 
        $result = $this->service->fetch(
            $request->input('role'), 
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // DELETE USER
    // =========================================
    public function delete(Request $request)
    {
        $result = $this->service->delete(
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // SWITCH ACCOUNT
    // =========================================
    public function switch(Request $request)
    {
        $result = $this->service->switch(
            $request->input('email'), 
            $request->input('role')
        );
        return $this->response->flash($result);
    }
}
