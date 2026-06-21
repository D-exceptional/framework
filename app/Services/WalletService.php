<?php
namespace App\Services;

use App\Http\Response;
use App\Support\TextManager;
use App\Queue\Queue;
use App\Jobs\SimpleMailJob;
use App\Jobs\PushNotificationJob;
use App\Support\CurrencyManager;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Notification;

class WalletService 
{
    protected Response $response;
    protected TextManager $textProcessor;
    protected Queue $queueManager;
    protected CurrencyManager $currencyManager;
    protected Wallet $walletModel;
    protected User $userModel;
    protected Notification $notificationModel;
    protected string $secretKey;
    protected string $currency;
    private string $baseUrl;

    public function __construct(
        Response $response,
        TextManager $textProcessor,
        Queue $queueManager,
        CurrencyManager $currencyManager,
        Wallet $walletModel, 
        User $userModel, 
        Notification $notificationModel
    )
    {
        $this->response          = $response;
        $this->textProcessor     = $textProcessor;
        $this->queueManager      = $queueManager;
        $this->currencyManager   = $currencyManager;
        $this->walletModel       = $walletModel;
        $this->userModel         = $userModel;
        $this->notificationModel = $notificationModel;
        $this->secretKey         = $_ENV['FLW_SECRET_KEY']; 
        $this->currency          = $_ENV['BASE_CURRENCY']; 
        $this->baseUrl           = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) ? 'http://localhost/projects/showcase/jobspot' : ''; 
    }

    // =========================================
    // CREATE PAYMENT
    // =========================================
    public function createPayment(
        float $amount, 
        string $channel, 
        int $facilitatorId, 
        int $userId, 
        string $identifier, 
        string $narration, 
        string $receipt
    ): array { 

        $reference = $this->walletModel->createPayment($amount, $channel, $facilitatorId, $userId, $identifier, $narration, $receipt);
        if ($reference === null) {
            return $this->response->fail('Failed to generate reference', 500);
        }

        $userDetails = $this->userModel->findById($userId);
        $userName    = $userDetails['fullname'];
        $userEmail   = $userDetails['email'];
        $userContact = $userDetails['contact'];

        $data = [
            'reference' => $reference, 
            'user' => [
                'name' => $userName, 
                'email' => $userEmail, 
                'phone' => $userContact
            ]
        ];
        
        return $this->response->success('Payment reference generated', $data);
    }

    // =========================================
    // UPDATE PAYMENT DETAILS
    // =========================================
    public function updateDetails(
        int $account, 
        string $bank, 
        string $code, 
        int $userId
    ): array { 

        $updated = $this->walletModel->updateDetails($account, $bank, $code, $userId);
        if ($updated === false) {
            return $this->response->fail('Failed to update details', 400);
        }

        return $this->response->success('Details updated successfully');
    }

    // =========================================
    // REQUEST WITHDRAWAL
    // =========================================
    public function requestFunds(
        int $amount, 
        string $description,
        string $narration,  
        array $user
    ): array {

        $userId   = $user['id'];
        $userName = $user['name'];
        $userType = ucfirst($user['role']);

        $bankDetails     = $this->walletModel->getBankDetails($userId);
        $bank            = $bankDetails['bank_name'];
        $account         = $bankDetails['account_number'];
        $withdrawalTable = $this->getWithdrawalTable($description);

        if (!$bank || $bank === 'None' || !$account || $account === 0) {
            return $this->response->fail('Bank details not available. Enter bank details via the profile page to proceed', 400);
        }

        $balance = $this->walletModel->getBalance($withdrawalTable, $userId);
        if ($balance === false) {
           return $this->response->fail('Failed to get balance', 500);
        }

        if ($balance === 0 || $amount > $balance) {
            return $this->response->fail('Insufficient balance', 400);
        }

        $processedWithdrawal = $this->currencyManager->format((float) $amount); // In Naira

        $request = $this->walletModel->requestFunds($amount, $bank, $account, $narration, $userId);
        if ($request === false) {
           return $this->response->fail('Failed to place withdrawal', 500);
        }

        $this->walletModel->debitWallet($withdrawalTable, $amount, $userId);

        $userMessage = "
            Hi <b>{$userName}</b>, 
            <br> You have successfully placed a withdrawal of <b>{$processedWithdrawal}</b>. 
            <br> A total of <b>{$processedWithdrawal}</b> will be paid into your bank account shortly. 
            <br> Have a great day ahead.
        ";

        $notification = $this->notificationModel->create($userMessage, 'Fund Request', $userId);
        if ($notification === false) {
            $this->logError("Failed to create notification for user: {$userName}");
        }

        $mail = [
            'subject' => 'Withdrawal Initiated',
            'message' => $userMessage,
        ];

        // Simple email job dispatch
        $this->queueManager->dispatch(
            SimpleMailJob::class,
            [
                $mail['subject'],
                $user['email'],         
                $mail['message']
            ],
            'emails'
        );

        $userPushMessage = $this->textProcessor->formatPushMessage($userMessage);

        // Push notification job dispatch
        $this->queueManager->dispatch(
            PushNotificationJob::class,
            [   
                "Single {$userType}", 
                $userId, 
                'Withdrawal Initiated', 
                $userPushMessage, 
                ['url' => "{$this->baseUrl}/login", 'type' => 'withdrawal']
            ],
            'push'
        );

        return $this->response->success('Withdrawal successful');
    }

    // =========================================
    // SINGLE TRANSFER
    // =========================================
    public function singleTransfer(
        string $bank,
        int $account,
        int $amount,
        string $narration,
        string $currency,
        string $reference,
        string $name
    ): array {

        $url = "https://api.flutterwave.com/v3/transfers";

        $body = [
            "account_bank"     => $bank, // Bank code is used (e.g 044)
            "account_number"   => $account,
            "amount"           => $amount,
            "narration"        => $narration,
            "currency"         => $currency,
            "reference"        => $reference,
            "debit_currency"   => $this->currency, // NGN by default
            "beneficiary_name" => $name,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->secretKey}",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['status']) && $result['status'] === 'success') {
            
            return $this->response->success('Transfer queued successfully', $result);
        } else {
            
            return $this->response->fail('Transfer queue failed', 400, $result);
        }
    }

    // =========================================
    // BULK TRANSFER
    // =========================================
    public function bulkTransfer(
        string $title,
        array $payouts
    ): array {

        $url = "https://api.flutterwave.com/v3/bulk-transfers";

        $body = [
            "title"     => $title,
            "bulk_data" => $payouts
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->secretKey}",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['status']) && $result['status'] === 'success') {
        
            // $totalAmount = array_sum(array_column($payouts, 'amount'));

            return $this->response->success('Bulk transfer queued successfully', $result);
        } else {
            
            return $this->response->fail('Bulk transfer queue failed', 400, $result);
        }
    }

    // =========================================
    // GET PAYMENT BY REFERENCE
    // =========================================
    public function getByReference(
        string $type,
        string $reference
    ): array {

        $payment = $this->walletModel->getOne($type, $reference);
        if ($payment === false) {
            return $this->response->fail('Failed to fetch payment', 500);
        }
        
        return $this->response->success('Payment fetched', ['payment' => $payment]);
    }

    // =========================================
    // GET PAYMENTS BY USERS
    // =========================================
    public function getPaymentsByUser(
        string $type, 
        int $userId,
        int $page
    ): array {

        $payments = $this->walletModel->getPaymentsByUser($type, $userId, $page);
        if ($payments === false) {
            return $this->response->fail('Failed to fetch payments', 400);
        }
        
        return $this->response->success('Payments fetched', $payments);
    }

    // =========================================
    // GET PAYMENTS BY TYPE
    // =========================================
    public function getPaymentsByType(
        string $type, 
        int $page
    ): array {

        $payments = $this->walletModel->getPaymentsByType($type, $page);
        if ($payments === false) {
            return $this->response->fail('Failed to fetch payments', 400);
        }
        
        return $this->response->success('Payments fetched', ['payments' => $payments]);
    }

    // =========================================
    // GET PAYMENTS BY STATUS
    // =========================================
    public function getPaymentsByStatus(
        string $type,
        string $status, 
        int $page
    ): array {

        $payments = $this->walletModel->getPaymentsByStatus($type, $status, $page);
        if ($payments === false) {
            return $this->response->fail('Failed to fetch payments', 400);
        }
        
        return $this->response->success('Payments fetched', ['payments' => $payments]);
    }

    // =========================================
    // GET PAYOUTS BY STATUS
    // =========================================
    public function getPayoutsByStatus(
        string $status, 
        int $page
    ): array {

        $payments = $this->walletModel->getWithdrawalsByStatus($status, $page);
        if (count($payments['payments']) === 0) {
            return $this->response->fail('Failed to fetch payments', 400);
        }
        
        return $this->response->success('Payments fetched', $payments);
    }

    // =========================================
    // GET PAYMENTS BY CHANNEL
    // =========================================
    public function getPaymentsByChannel(
        string $channel, 
        string $status, 
        int $page
    ): array {

        $payments = $this->walletModel->getPaymentsByChannel($channel, $status, $page);
        if (count($payments['payments']) === 0) {
            return $this->response->fail('Failed to fetch payments', 400);
        }
        
        return $this->response->success('Payments fetched', $payments);
    }

    // =========================================
    // EXTRACT DATA FROM REFERENCE
    // =========================================
    private function decodeReference(
        string $reference
    ): array {

        $parts = explode('-', $reference);

        if (count($parts) < 3) {
            return [null, null];
        }

        return [
            'type' => strtoupper($parts[1]),  // WAL or WIT
            'date' => $parts[2]               // optional use
        ];
    }

    // =========================================
    // FINALIZE TRANSACTION
    // =========================================
    private function finalizeTransaction(
        ?string $reference = null, 
        ?string $status = null, 
        ?float $amount = null, 
        ?string $denomination = null
    ): bool {

        $decoded = $this->decodeReference($reference);
        $type = $decoded['type'];

        if ($type === 'REG') {
            return $this->finalizeRegistrationPayment($reference, $status, $amount);
        }

        if ($type === 'WIT') {
            return $this->finalizeWithdrawal($reference, $status);
        }

        return false;
    }

    // =========================================
    // FINALIZE WITHDRAWAL PAYOUT
    // =========================================
    private function finalizeWithdrawal(
        string $reference, 
        string $status
    ): bool {

        $record = $this->walletModel->getByReference('withdrawals', 'reference', $reference, 'withdrawal_status', 'Pending');
        if (!$record) return false;

        if (in_array($record['status'], ['Completed', 'Failed'])) return true;

        $userId      = $record['user_id'];
        $userDetails = $this->userModel->findById($userId);
        $userName    = $userDetails['fullname'];
        $userEmail   = $userDetails['email'];
        $userType    = $userDetails['user_role'];

        if ($status === 'successful') {

            $amount = $amount ?: $record['amount'];

            $processedAmount = $this->currencyManager->format((float) $amount);

            $this->walletModel->updateWithdrawalStatus('Completed', $reference);

            $payoutMessage = "
                Hi <b>{$userName}</b>, 
                <br> You have received a payout of <b>{$processedAmount}</b> from Jobspot.
                <br> Your transaction reference is: <b>{$reference}</b>.
                <br> We hope to see more sales from your stores</b>.
                <br> Have a great day ahead.
            ";

            $notification = $this->notificationModel->create($payoutMessage, 'Fund Payout', $userId);
            if ($notification === false) {
                $this->logError("Failed to create notification for user: {$userName}");
            }

            $mail = [
                'subject' => 'Jobspot Payout',
                'message' => $payoutMessage,
            ];

            $userPushMessage = $this->textProcessor->formatPushMessage($payoutMessage);

            // Simple email job dispatch
            $this->queueManager->dispatch(
                SimpleMailJob::class,
                [
                    $mail['subject'],
                    $userEmail,
                    $mail['message']
                ],
                'emails'
            );

            // Push notification job dispatch
            $this->queueManager->dispatch(
                PushNotificationJob::class,
                [
                    "Single {$userType}", 
                    $userId, 
                    'Company Payout', 
                    $userPushMessage, 
                    ['url' => "{$this->baseUrl}/login", 'type' => 'payout']
                ],
                'push'
            );

            return true;
        }

        if ($status === 'failed') {
            $this->walletModel->updateWithdrawalStatus('Failed', $reference);
            return true;
        }

        $this->walletModel->updateWithdrawalStatus(ucfirst($status), $reference);
        return false;
    }

    // =========================================
    // PROCESS COMMISSIONS
    // =========================================
    private function processCommissions(
        int $facilitatorId
    ): bool {

        define('TOP_LEVEL_ADMINS', json_encode([1, 3]));
        define('DIRECT_ADMIN_COMMISSION', 9000);
        define('DIRECT_SAVINGS_COMMISSION', 1000);
        define('SHARED_ADMIN_COMMISSION', 5000);  
        define('SHARED_SAVINGS_COMMISSION', 1000);
        define('AFFILIATE_COMMISSION', 4000);

        $topAdmins          = json_decode(TOP_LEVEL_ADMINS);
        $isAdminRegistraion = in_array($facilitatorId, $topAdmins);

        if ($isAdminRegistraion) {
            $this->distributeAdminCommission(DIRECT_ADMIN_COMMISSION, 'Admin');
            $this->walletModel->updateLegacyWallet('wallet_savings', DIRECT_SAVINGS_COMMISSION);
            return true;
        }
        else {
            $this->walletModel->updateLegacyWallet('wallet_savings', SHARED_SAVINGS_COMMISSION);
            $this->walletModel->updateAffiliateWallet(AFFILIATE_COMMISSION, $facilitatorId);
            $this->distributeAdminCommission(SHARED_ADMIN_COMMISSION, 'Leader');
            return true;
        }
    }

    // =========================================
    // DISTRIBUTE ADMIN COMMISSIONS
    // =========================================
    private function distributeAdminCommission(
        float $amount, 
        string $type
    ): void {

        $wisdomCommission   = $type === 'Admin' ? $amount * 0.90 : $amount * 0.90;
        $thompsonCommission = $type === 'Admin' ? $amount * 0.10 : $amount * 0.10;

        $adminCommissions = [
            1 => $wisdomSavings,    
            3 => $thompsonCommission 
        ];

        foreach ($adminCommissions as $adminId => $commission) {
            $this->walletModel->creditWallet('wallet_admin', $commission, $adminId);
        }
    }

    // =========================================
    // FINALIZE REGISTRATION PAYMENT
    // =========================================
    private function finalizeRegistrationPayment(
        ?string $reference = null, 
        ?string $status = null, 
        ?float $amount = null
    ): bool {

        $record = $this->walletModel->getByReference('membership_payment', 'payment_ref', $reference, 'payment_status', 'Pending');
        if (!$record) return false;

        if (in_array($record['status'], ['Completed', 'Failed'])) return true;

        $userId         = $record['user_id'];
        $userDetails    = $this->userModel->findById($userId);
        $userName       = $userDetails['fullname'];
        $userEmail      = $userDetails['email'];
        $userType       = strtolower($userDetails['user_role']);
        $facilitatorId  = $record['facilitator_id'];

        if ($status === 'successful') {

            $amount = $amount ?: $record['amount'];

            $this->userModel->activateAccount($userId);
            $this->processCommissions($facilitatorId);
            $this->walletModel->updatePaymentStatus('Completed', $reference);

            $mail = [
                'subject' => 'Successful Registration',
                'message' => "
                    Hi <b>{$userName}</b>, 
                    <br> You have successfully registered on Jobspot. 
                    <br> Login to your dashboard here: <b><a href='{$this->baseUrl}/login'>Login to dashboard</a></b>
                    <br> We are happy to have you onboard.
                ",
            ];

            $userPushMessage = $this->textProcessor->formatPushMessage($mail['message']);

            // Simple email job dispatch
            $this->queueManager->dispatch(
                SimpleMailJob::class,
                [
                    $mail['subject'],
                    $userEmail,
                    $mail['message']
                ],
                'emails'
            );

            // Push notification job dispatch
            $this->queueManager->dispatch(
                PushNotificationJob::class,
                [
                    "Single {$userType}", 
                    $userId, 
                    'Successful Registration', 
                    $userPushMessage,
                    ['url' => "{$this->baseUrl}/login", 'type' => 'registration']
                ],
                'push'
            );

            if (!in_array($facilitatorId, [1, 2, 3])) {

                $affiliateDetails = $this->userModel->findById($facilitatorId);
                $affiliateName    = $affiliateDetails['fullname'];
                $affiliateEmail   = $affiliateDetails['email'];

                $affiliateMessage = "
                    Congratulations {$affiliateName}!
                    <br> You've earned a commission for a successful referral.
                    <br> You can access your account to verify this transaction.
                    <br> Login to your dashboard here: <b><a href='{$this->baseUrl}/login'>Login to dashboard</a></b>
                    <br> We look forward to seeing more referrals from you.
                ";

                $notification = $this->notificationModel->create($affiliateMessage, 'New Commission', $facilitatorId);
                if ($notification === false) {
                    // return $this->response->fail('Failed to create notification for user', 500);
                    $this->logError("Failed to create notification for affiliate: {$affiliateName}");
                }

                $mail = [
                    'subject' => 'New Commission',
                    'message' => $affiliateMessage,
                ];

                $affiliatePushMessage = $this->textProcessor->formatPushMessage($affiliateMessage);

                // Simple email job dispatch
                $this->queueManager->dispatch(
                    SimpleMailJob::class,
                    [
                        $mail['subject'],
                        $affiliateEmail,
                        $mail['message']
                    ],
                    'emails'
                );

                // Push notification job dispatch
                $this->queueManager->dispatch(
                    PushNotificationJob::class,
                    [
                        'Single Leader', 
                        $facilitatorId, 
                        'New Commission', 
                        $affiliatePushMessage, 
                        ['url' => "{$this->baseUrl}/login", 'type' => 'commission']
                    ],
                    'push'
                );
            }

            $adminMessage =  "
                Hello Admin, 
                <br> A new {$userType}, <b>{$userName}</b>, just registered on the platform!
                <br> Kindly review and take necessary actions. 
            ";

            $adminPushMessage = $this->textProcessor->formatPushMessage($adminMessage);

            $admins = $this->userModel->allByRole('Admin');

            foreach ($admins as $admin) {

                // Simple email job dispatch
                $this->queueManager->dispatch(
                    SimpleMailJob::class,
                    [
                        'New Registration', 
                        $admin['email'], 
                        $adminMessage
                    ],
                    'emails'
                );

                // Push notification job dispatch
                $this->queueManager->dispatch(
                    PushNotificationJob::class,
                    [
                        'Single Admin', 
                        $admin['user_id'], 
                        'New Registration', 
                        $adminPushMessage, 
                        ['url' => "{$this->baseUrl}/admin", 'type' => 'registration']
                    ],
                    'push'
                );
                
                $notification = $this->notificationModel->create($adminMessage, 'New Registration', $admin['user_id']);
                if ($notification === false) {
                    $this->logError("Failed to create notification for admin: {$admin['email']}");
                }
            }

            return true;
        }

        if ($status === 'failed') {
            $this->walletModel->updatePaymentStatus('Failed', $reference);
            return true;
        }

        // Any other state → pending
        $this->walletModel->updatePaymentStatus(ucfirst($status), $reference);
        return false;
    }

    // =========================================
    // AUTO VERIFY PAYMENT
    // =========================================
    public function verifyPaymentAuto(
        int $id,
        string $reference
    ): array {

        $url = "https://api.flutterwave.com/v3/transactions/{$id}/verify";
        $response = curl_exec(curl_init($url));
        $result = json_decode($response, true);

        if (!isset($result['data']['status'])) {
            return $this->response->success("We are waiting for payment confirmation", ['pending' => true]);
        }

        $status       = strtolower($result['data']['status']);
        $amount       = $result['data']['amount'] ?? null;
        $denomination = $result['data']['currency'] ?? null;

        $done = $this->finalizeTransaction($reference, $status, $amount, $denomination);

        if ($done) {
            return $this->response->success("Transaction processed", ['status' => $status]);
        }

        return $this->response->success("Transaction pending, awaiting webhook...");
    }

    // =========================================
    // MANUALLY VERIFY PAYMENT
    // =========================================
    public function verifyPaymentManual(
        string $reference
    ): array {

        $record = $this->walletModel->getByReference('membership_payment', 'payment_ref', $reference, 'payment_status', 'Pending');
        if (!$record) {
            return $this->response->fail('Payment not found', 404);
        } 

        if (in_array($record['status'], ['Completed', 'Failed'])) {
            return $this->response->fail('Payment already processed', 400);
        } 

        $userId        = $record['user_id'];
        $userDetails   = $this->userModel->findById($userId);
        $userName      = $userDetails['fullname'];
        $userEmail     = $userDetails['email'];
        $userType      = $userDetails['user_role'];
        $userRole      = strtolower($userType);
        $facilitatorId = $record['facilitator_id'];

        $this->userModel->activateAccount($userId);
        $this->processCommissions($facilitatorId);
        $this->walletModel->updatePaymentStatus('Completed', $reference);

        $mail = [
            'subject' => 'Successful Registration',
            'message' => "
                Hi <b>{$userName}</b>, 
                <br> You have successfully registered on Jobspot. 
                <br> Login to your dashboard here: <b><a href='{$this->baseUrl}/login'>Login to dashboard</a></b>
                <br> We are happy to have you onboard.
            ",
        ];

        $userPushMessage = $this->textProcessor->formatPushMessage($mail['message']);

        // Simple email job dispatch
        $this->queueManager->dispatch(
            SimpleMailJob::class,
            [
                $mail['subject'],
                $userEmail,
                $mail['message']
            ],
            'emails'
        );

        // Push notification job dispatch
        $this->queueManager->dispatch(
            PushNotificationJob::class,
            [
                "Single {$userType}", 
                $userId, 
                'Successful Registration', 
                $userPushMessage,
                ['url' => "{$this->baseUrl}/login", 'type' => 'registration']
            ],
            'push'
        );

        if (!in_array($facilitatorId, [1, 2, 3])) {

            $affiliateDetails = $this->userModel->findById($facilitatorId);
            $affiliateName    = $affiliateDetails['fullname'];
            $affiliateEmail   = $affiliateDetails['email'];

            $affiliateMessage = "
                Congratulations {$affiliateName}!
                <br> You've earned a commission for a successful referral.
                <br> You can access your account to verify this transaction.
                <br> Login to your dashboard here: <b><a href='{$this->baseUrl}/login'>Login to dashboard</a></b>
                <br> We look forward to seeing more referrals from you.
            ";

            $notification = $this->notificationModel->create($affiliateMessage, 'New Commission', $facilitatorId);
            if ($notification === false) {
                // return $this->response->fail('Failed to create notification for user', 500);
                $this->logError("Failed to create notification for affiliate: {$affiliateName}");
            }

            $mail = [
                'subject' => 'New Commission',
                'message' => $affiliateMessage,
            ];

            $affiliatePushMessage = $this->textProcessor->formatPushMessage($affiliateMessage);

            // Simple email job dispatch
            $this->queueManager->dispatch(
                SimpleMailJob::class,
                [
                    $mail['subject'],
                    $affiliateEmail,
                    $mail['message']
                ],
                'emails'
            );

            // Push notification job dispatch
            $this->queueManager->dispatch(
                PushNotificationJob::class,
                [
                    'Single Leader', 
                    $facilitatorId, 
                    'New Commission', 
                    $affiliatePushMessage, 
                    ['url' => "{$this->baseUrl}/login", 'type' => 'commission']
                ],
                'push'
            );
        }

        $adminMessage =  "
            Hello Admin, 
            <br> A new {$userRole}, <b>{$userName}</b>, just registered on the platform!
            <br> Kindly review and take necessary actions. 
        ";

        $adminPushMessage = $this->textProcessor->formatPushMessage($adminMessage);

        $admins = $this->userModel->allByRole('Admin');

        foreach ($admins as $admin) {

            // Simple email job dispatch
            $this->queueManager->dispatch(
                SimpleMailJob::class,
                [
                    'New Registration',
                    $admin['email'],
                    $adminMessage
                ],
                'emails'
            );

            // Push notification job dispatch
            $this->queueManager->dispatch(
                PushNotificationJob::class,
                [
                    'Single Admin', 
                    $admin['user_id'], 
                    'New Registration', 
                    $adminPushMessage, 
                    ['url' => "{$this->baseUrl}/admin", 'type' => 'registration']
                ],
                'push'
            );
            
            $notification = $this->notificationModel->create($adminMessage, 'New Registration', $admin['user_id']);
            if ($notification === false) {
                $this->logError("Failed to create notification for admin: {$admin['email']}");
            }
        }

        return $this->response->success('Payment processed and completed successfully', 200);
    }

    // =========================================
    // GET PAYMENT SUMMARY
    // =========================================
    public function getPaymentSummary(
        string $view, 
        int $userId, 
        string $period, 
        string $startDate, 
        string $endDate
    ): array {  

        $stats = $this->walletModel->getPaymentAndRevenueByPeriod($view, $userId, $period, $startDate, $endDate);

        return $this->response->success('Stats fetched', $stats);
    }

    // =========================================
    // GET TABLE
    // =========================================
    private function getWithdrawalTable(
        string $description
    ): string {
        
        $maps = [
            'Admin Withdrawal'    => 'wallet_admin',
            'Task Withdrawal'     => 'wallet_task',
            'Referral Withdrawal' => 'wallet_referrals',
            'Bonus Withdrawal'    => 'wallet_bonus'
        ];

        return $maps[$description];
    }

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
