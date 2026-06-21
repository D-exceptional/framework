<?php
namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\WalletService;

class WalletController 
{
    protected Response $response;
    protected WalletService $service;

    public function __construct(Response $response, WalletService $service)
    {
        $this->response = $response;
        $this->service  = $service;
    }

    // =========================================
    // CREATE PAYMENT
    // =========================================
    public function createPayment(Request $request)
    { 
        $userId = $request->user()['id'];
        $result = $this->service->createPayment(
            (int) $request->input('amount'), 
            $request->input('channel'), 
            $request->input('facilitatorId'), 
            $userId,
            $request->input('identifier'), 
            $request->input('narration'), 
            $request->input('receipt')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // UPDATE PAYMENT DETAILS
    // =========================================
    public function updateDetails(Request $request)
    { 
        $userId = $request->user()['id'];
        $result = $this->service->updateDetails(
            (int) $request->input('account'), 
            $request->input('bank'), 
            $request->input('code'), 
            $userId
        );
        return $this->response->flash($result);
    }

    // =========================================
    // REQUEST WITHDRAWAL
    // =========================================
    public function requestFunds(Request $request)
    {
        $user = $request->user();
        $result = $this->service->requestFunds(
            (int) $request->input('amount'), 
            $request->input('description'), 
            $request->input('narration'),
            $user
        );
        return $this->response->flash($result);
    }

    // =========================================
    // SINGLE TRANSFER
    // =========================================
    public function singleTransfer(Request $request)
    {
        $result = $this->service->singleTransfer(
            $request->input('bank'),
            (int) $request->input('account'), 
            (int) $request->input('amount'), 
            $request->input('narration'),
            $request->input('currency'),
            $request->input('reference'),
            $request->input('name'),
        );
        return $this->response->flash($result);
    }

    // =========================================
    // BULK TRANSFER
    // =========================================
    public function bulkTransfer(Request $request)
    {
        $result = $this->service->bulkTransfer(
            $request->input('title'),
            $request->input('bulk_data')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET PAYMENT BY REFERENCE
    // =========================================
    public function getByReference(Request $request)
    {
        $result = $this->service->getByReference(
            $request->input('type'),
            $request->input('reference')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET PAYMENTS BY USER
    // =========================================
    public function getPaymentsByUser(Request $request)
    {
        $userId = $request->user()['id'];
        $result = $this->service->getPaymentsByUser(
            $request->input('type'),
            $userId,
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET PAYMENTS BY TYPE
    // =========================================
    public function getPaymentsByType(Request $request)
    {
        $result = $this->service->getPaymentsByType(
            $request->input('type'),
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET PAYMENTS BY STATUS
    // =========================================
    public function getPaymentsByStatus(Request $request)
    {
        $result = $this->service->getPaymentsByStatus(
            $request->input('type'),
            $request->input('status'),
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET PAYOUTS BY STATUS
    // =========================================
    public function getPayoutsByStatus(Request $request)
    {
        $result = $this->service->getPayoutsByStatus(
            $request->input('status'),
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // AUTO VERIFY PAYMENT
    // =========================================
    public function verifyPaymentAuto(Request $request)
    {
        $result = $this->service->verifyPaymentAuto(
            (int) $request->input('id'),
            $request->input('reference')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // MANUALLY VERIFY PAYMENT
    // =========================================
    public function verifyPaymentManual(Request $request)
    {
        $result = $this->service->verifyPaymentManual(
            $request->input('reference')
        );
        return $this->response->flash($result);
    }

    // =========================================
    // GET PAYMENT SUMMARY
    // =========================================
    public function getPaymentSummary(Request $request)
    {  
        // Collect query params
        $view       = $request->input('view') ?? 'admin';
        $period     = $request->input('period') ?? 'today';
        $startDate  = $request->input('start') ?? null;
        $endDate    = $request->input('end') ?? null;

        // Get sales summary
        $userId = $request->user()['id'];
        $result = $this->service->getPaymentSummary($view, $userId, $period, $startDate, $endDate);
        return $this->response->flash($result);
    }

    // =========================================
    // GET PAYMENTS BY CHANNEL
    // =========================================
    public function getPaymentsByChannel(Request $request)
    {
        $result = $this->service->getPaymentsByChannel(
            $request->input('channel'),
            $request->input('status'),
            (int) $request->input('page')
        );
        return $this->response->flash($result);
    }
}
