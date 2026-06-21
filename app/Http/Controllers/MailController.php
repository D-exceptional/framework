<?php

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\MailService;

class MailController 
{ 
    protected Response $response;
    protected MailService $service;

    public function __construct(Response $response, MailService $service)
    {                                                                                        
        $this->response = $response;
        $this->service  = $service;                                                                                                                                                                                                                                                                                                      
    }

    // =========================================
    // COUNT INBOX
    // =========================================
    public function countInbox(Request $request)
    {
        $email = $request->user()['email'];
        $result = $this->service->countInbox($email);
        return $this->response->flash($result);
    }

    // =========================================
    // COUNT OUTBOX
    // =========================================
    public function countOutbox(Request $request)
    {
        $name = $request->user()['name'];
        $result = $this->service->countOutbox($name);
        return $this->response->flash($result);
    }

    // =========================================
    // GET INBOX MESSAGES
    // =========================================
    public function getInbox(Request $request)
    {
        $email = $request->user()['email'];
        $result = $this->service->getInbox(
            $email, 
            (int) $request->input('page')
        );      
        return $this->response->flash($result);
    }

    // =========================================
    // GET OUTBOX MESSAGES
    // =========================================
    public function getOutbox(Request $request)
    {
        $name = $request->user()['name'];
        $result = $this->service->getOutbox(
            $name, 
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET SINGLE MAIL DATA
    // =========================================
    public function getMail(Request $request)
    {
        $result = $this->service->getMail(
            (int) $request->input('id')
        );
        return $this->response->flash($result);                
    }

    // =========================================
    // DELETE MAIL
    // =========================================
    public function deleteMail(Request $request)
    {
        $result = $this->service->deleteMail(
            (int) $request->input('id')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // SUBSCRIBE TO MAIL LIST
    // =========================================
    public function subscribeMail(Request $request)
    {
        $result = $this->service->subscribeMail(
            $request->input('email')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // SEND BULK MAIL
    // =========================================
    public function sendBulk(Request $request)
    {
        $file = $request->file('attachment');
        $hasAttachment = isset($file) && !empty($file);

        $result = $this->service->sendBulk(
            $request->input('recipients'),
            $request->input('subject'),
            $request->input('message'),
            $request->input('sender'),
            $hasAttachment,
            $file
        );
        return $this->response->flash($result);
    }
}
