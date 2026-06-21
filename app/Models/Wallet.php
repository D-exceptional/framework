<?php
namespace App\Models;

class Wallet extends Model
{
    protected string $table = 'wallets';

    // =========================================
    // GENERATE REGISTRATION REFERENCE
    // =========================================
    public function generatePaymentReference(
        string $type = 'Registration', 
        string $identifier = 'ADM'
    ): string {

        $prefix = 'PAY';
        $type   = strtoupper(substr($type, 0, 3));
        $date   = date('ymd');
        $random = strtoupper(bin2hex(random_bytes(4)));

        return "{$prefix}-{$type}-{$date}-{$random}-{$identifier}";
    }

    // =========================================
    // GENERATE WITHDRAWAL REFERENCE
    // =========================================
    public function generateWithdrawalReference(
        string $type = 'Request', 
        string $identifier = 'SYS'
    ): string {

        $prefix = 'PAY';
        $type   = strtoupper(substr($type, 0, 3));
        $date   = date('ymd');
        $random = strtoupper(bin2hex(random_bytes(4)));

        return "{$prefix}-{$type}-{$date}-{$random}-{$identifier}";
    }

    // =========================================
    // CREATE PAYMENT REFERENCE
    // =========================================
    public function createPayment(
        float $amount, 
        string $channel, 
        int $facilitatorId, 
        int $userId, 
        string $identifier, 
        string $narration, 
        string $receipt
    ): string {

        // Generate reference
        $reference = $this->generatePaymentReference('Registration', $identifier);

        // Define payment tables
        $tables = ['membership_payment', 'membership_payment_backup'];

        // Save records
        foreach ($tables as $table) {

            $this->executeQuery(
                "INSERT INTO {$table} (payment_amount, payment_ref, payment_channel, facilitator_id, user_id) VALUES (?, ?, ?, ?, ?)",
                [$amount, $reference, $channel, $facilitatorId, $userId]
            );
        }

        // Save receipt for manual payments
        if ($narration === 'Manual') {

            $this->executeQuery(
                "INSERT INTO membership_payment_receipts (receipt_name, receipt_ref) VALUES (?, ?)",
                [$receipt, $reference]
            );
        }

        return $reference;
    }

    // =========================================
    // CREATE BANK DETAILS
    // =========================================
    public function createDetails(
        int $account, 
        string $bank, 
        string $code, 
        string $recipient, 
        string $currency, 
        int $userId
    ): bool {

        return $this->executeQuery(
            "INSERT INTO bank_details (account_number, bank_name, bank_code, recipient_code, currency_code, user_id) VALUES (?, ?, ?, ?, ?, ?)",
            [$account, $bank, $code, $recipient, $currency, $userId]
        );
    }

    // =========================================
    // UPDATE BANK DETAILS
    // =========================================
    public function updateDetails(
        int $account, 
        string $bank, 
        string $code, 
        int $userId
    ): bool {

        return $this->executeQuery(
            "UPDATE bank_details SET account_number = ?, bank_name = ?, bank_code = ? WHERE user_id = ?",
            [$account, $bank, $code, $userId]
        );
    }

    // =========================================
    // CREATE WALLETS
    // =========================================
    public function createWallet(
        int $amount, 
        int $userId
    ): void {

        $tables = ['wallet_task', 'wallet_task_backup'];

        foreach ($tables as $table) {
        
            $this->executeQuery(
                "INSERT INTO {$table} (wallet_amount, user_id) VALUES (?, ?)",
                [$amount, $userId]
            );
        }
    }

    // =========================================
    // CREDIT WALLET
    // =========================================
    public function creditWallet(
        string $table, 
        float $amount, 
        int $userId
    ): bool {

        return $this->executeQuery(
            "UPDATE {$table} SET wallet_amount = wallet_amount + ? WHERE user_id = ?",
            [$amount, $userId]
        );
    }

    // =========================================
    // DEBIT WALLET SAFELY
    // =========================================
    public function debitWallet(
        string $table, 
        float $amount, 
        int $userId
    ): bool {

        $sql = "UPDATE {$table} 
            SET wallet_amount = CASE 
                WHEN wallet_amount >= ? THEN wallet_amount - ? 
                ELSE wallet_amount 
            END
            WHERE user_id = ?
        ";

        return $this->executeQuery(
            $sql,
            [$amount, $amount, $userId]
        );
    }

    // =========================================
    // UPDATE LEGACY WALLET
    // =========================================
    public function updateLegacyWallet(
        string $table, 
        float $amount
    ): bool {

        return $this->executeQuery(
            "UPDATE {$table} SET wallet_amount = wallet_amount + ?",
            [$amount]
        );
    }

    // =========================================
    // UPDATE AFFILIATE WALLET
    // =========================================
    public function updateAffiliateWallet(
        float $amount, 
        int $userId
    ): void {

        $tables = ['wallet_referrals', 'wallet_referrals_backup'];

        foreach ($tables as $table) {
            $this->creditWallet($table, $amount, $userId);
        }
    }

    // =========================================
    // REQUEST FUNDS
    // =========================================
    public function requestFunds(
        float $amount, 
        string $bank, 
        int $account, 
        string $narration, 
        int $userId
    ): bool {

        $reference = $this->generateWithdrawalReference();

        return $this->executeQuery(
            "INSERT INTO withdrawals (amount, bank, account, reference, narration, user_id) VALUES (?, ?, ?, ?, ?, ?)",
            [$amount, $bank, $account, $reference, $narration, $userId]
        );
    }

    // =========================================
    // UPDATE PAYMENT STATUS
    // =========================================
    public function updatePaymentStatus(
        string $status, 
        string $reference
    ): void {

        $tables = ['membership_payment', 'membership_payment_backup'];

        foreach ($tables as $table) {

            $this->executeQuery(
                "UPDATE {$table} SET payment_status = ? WHERE payment_ref = ?",
                [$status, $reference]
            );
        }
    }

    // =========================================
    // UPDATE WITHDRAWAL STATUS
    // =========================================
    public function updateWithdrawalStatus(
        string $status, 
        string $reference
    ): bool {

        return $this->executeQuery(
            "UPDATE withdrawals SET withdrawal_status = ? WHERE reference = ?",
            [$status, $reference]
        );
    }

    // =========================================
    // GET BANK DETAILS
    // =========================================
    public function getBankDetails(
        int $userId
    ): ?array {

        return $this->queryOne(
            "SELECT * FROM bank_details WHERE user_id = ?",
            [$userId]
        );
    }

    // =========================================
    // GET PAYMENT DETAILS
    // =========================================
    public function getOne(
        string $type, 
        string $reference
    ): ?array {

        $table  = $type === 'Membership' ? 'membership_payment' : 'withdrawals';
        $column = $type === 'Membership' ? 'payment_ref' : 'reference';

        return $this->queryOne(
            "SELECT * FROM {$table} WHERE {$column} = ?",
            [$reference]
        );
    }

    // =========================================
    // GET PAYMENT DETAILS BY REFERENCE
    // =========================================
    public function getByReference(
        string $table, 
        string $referenceColumn, 
        string $reference, 
        string $statusColumn, 
        string $status
    ): ?array {

        return $this->queryOne(
            "SELECT * FROM {$table} WHERE {$referenceColumn} = ? AND {$statusColumn} = ?",
            [$table, $referenceColumn, $reference, $statusColumn, $status]
        );
    }

    // =========================================
    // GET TABLE DETAILS
    // =========================================
    private function getTable(
        string $type
    ): array {

        $maps = [
            'Payment'    => ['table' => 'membership_payment', 'status' => 'payment_status'],
            'Backup'     => ['table' =>'membership_payment_backup', 'status' => 'payment_status'],
            'Withdrawal' => ['table' =>'withdrawals', 'status' => 'withdrawal_status'],
        ];

        return $maps[$type];
    }

    // =========================================
    // GET PAYMENT BY USER
    // =========================================
    public function getPaymentsByUser(
        ?string $type = null, 
        ?int $userId = null, 
        int $page = 1, 
        int $limit = 20
    ): array {

        $data   = $this->getTable($type);
        $table  = $data['table'];
        $offset = ($page - 1) * $limit;

        return $this->queryAll(
            "SELECT * FROM {$table} WHERE user_id = ? ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            [$userId]
        );
    }

    // =========================================
    // GET PAYMENT BY TYPE
    // =========================================
    public function getPaymentsByType(
        ?string $type = null, 
        int $page = 1, 
        int $limit = 20
    ): array {

        $data   = $this->getTable($type);
        $table  = $data['table'];
        $offset = ($page - 1) * $limit;

        return $this->queryAll(
            "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            []
        );
    }

    // =========================================
    // GET PAYMENT BY STATUS
    // =========================================
    public function getPaymentsByStatus(
        ?string $type = null, 
        ?string $status = null, 
        int $page = 1, 
        int $limit = 20
    ): array {

        $data   = $this->getTable($type);
        $table  = $data['table'];
        $column = $data['status'];
        $offset = ($page - 1) * $limit;

        return $this->queryAll(
            "SELECT * FROM {$table} WHERE $column = ? ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            [$status]
        );
    }

    // =========================================
    // GET WALLET BALANCE
    // =========================================
    public function getBalance(
        string $table, 
        int $userId
    ): int {

        return $this->fetchColumn(
            "SELECT wallet_amount FROM {$table} WHERE user_id = ?",
            [$userId]
        );
    }

    // =========================================
    // GET LEGACY BALANCE
    // =========================================
    private function getLegacyBalance(
        string $table
    ): int {

        return $this->fetchColumn("SELECT wallet_amount FROM {$table}", []);
    }

    // =========================================
    // GET WITHDRAWALS BY TYPE
    // =========================================
    public function getWithdrawalByType(
        ?int $userId = null, 
        ?string $status = null, 
        string $role = 'user'
    ): int {

        $sql = "SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE 1";
        $params = [];

        // Vendor mode: restrict to vendor's user ID
        if ($role === 'user' && !is_null($userId)) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }

        // Optional status filter
        if (!is_null($status)) {
            $sql .= " AND withdrawal_status = ?";
            $params[] = $status;
        }

        return $this->fetchColumn($sql, $params);
    }

    // =========================================
    // GET PAYMENT SUM
    // =========================================
    private function getTotalPayment(
        ?string $table = null, 
        ?string $status = null
    ): int {

        $sql = "SELECT COALESCE(SUM(payment_amount), 0) FROM {$table} WHERE 1";
        $params = [];

        // Optional status filter
        if (!is_null($status)) {
            $sql .= " AND payment_status = ?";
            $params[] = $status;
        }

        return $this->fetchColumn($sql, $params);
    }

    // =========================================
    // GET ADMIN PAYMENT STATS
    // =========================================
    public function getAdminWalletStats(
        int $userId
    ): array {

        return [
            'completed_weekly_payment'     => $this->getTotalPayment('membership_payment', 'Completed'),
            'pending_weekly_payment'       => $this->getTotalPayment('membership_payment', 'Pending'),
            'completed_total_payment'      => $this->getTotalPayment('membership_payment_backup', 'Completed'),
            'company_savings_balance'      => $this->getLegacyBalance('wallet_savings'),
            'monthly_appreciation_balance' => $this->getLegacyBalance('wallet_appreciation'),
            'personal_current_balance'     => $this->getBalance('wallet_admin', $userId),
            'pending_payout'               => $this->getWithdrawalByType(null, 'Pending', 'admin'),
            'total_payout'                 => $this->getWithdrawalByType(null, 'Completed', 'admin'),
            'leader_blessing_bonus'        => $this->getBalance('wallet_bonus', 88),
            'leader_confidence_bonus'      => $this->getBalance('wallet_bonus', 89),
        ];
    }

    // =========================================
    // GET USER PAYMENT STATS
    // =========================================
    public function getUserWalletStats(
        int $userId
    ): array {

        return [
            'task_balance'    => $this->getBalance('wallet_task', $userId),
            'task_total'      => $this->getBalance('wallet_task_backup', $userId),
            'pending_payout'  => $this->getWithdrawalByType($userId, 'Pending', 'user'),
            'total_payout'    => $this->getWithdrawalByType($userId, 'Completed', 'user'),
        ];
    }

    // =========================================
    // GET LEADER PAYMENT STATS
    // =========================================
    public function getLeaderWalletStats(
        int $userId, 
        string $rank
    ): array {

        return [
            'referral_balance' => $this->getBalance('wallet_referrals', $userId),
            'referral_total'   => $this->getBalance('wallet_referrals_backup', $userId),
            'task_balance'     => $this->getBalance('wallet_task', $userId),
            'task_total'       => $this->getBalance('wallet_task_backup', $userId),
            'pending_payout'   => $this->getWithdrawalByType($userId, 'Pending', 'user'),
            'total_payout'     => $this->getWithdrawalByType($userId, 'Completed', 'user'),
            'bonus_balance'    => $rank === 'Top' ? $this->getBalance('wallet_bonus', $userId) : 0,
            'bonus_total'      => $rank === 'Top' ? $this->getBalance('wallet_bonus_backup', $userId) : 0,
            'monthly_balance'  => $rank === 'Top' ? $this->getLegacyBalance('wallet_appreciation') : 0,
        ];
    }

    // =========================================
    // GET WITHDRAWALS BY STATUS
    // =========================================
    public function getWithdrawalsByStatus(
        string $status, 
        int $page = 1, 
        int $limit = 20
    ): ?array {

        $offset = ($page - 1) * $limit;

        // Fetch withdrawals with user & bank details
        $sql = "
            SELECT 
                w.withdrawal_id,
                w.amount,
                w.bank,
                w.account,
                w.reference,
                w.narration,
                w.withdrawal_status,
                w.created_at,

                u.user_id,
                u.fullname,
                u.email,
                u.country,

                bd.account_number AS account_number,
                bd.bank_name AS bank_name,
                bd.bank_code AS bank_code,
                bd.currency_code AS currency_code

            FROM withdrawals w
            INNER JOIN users u ON w.user_id = u.user_id
            LEFT JOIN bank_details bd ON w.user_id = bd.user_id
            WHERE w.withdrawal_status = ?
            ORDER BY w.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $payments = $this->queryAll($sql, [$status]);

        $total = $this->fetchColumn(
            "SELECT COUNT(*) FROM withdrawals WHERE withdrawal_status = ?", 
            [$status]
        );

        $sum = $this->fetchColumn(
            "SELECT SUM(amount) FROM withdrawals WHERE withdrawal_status = ?", 
            [$status]
        );

        return $this->format($payments, $total, $sum, $page, $limit);
    }

    // =========================================
    // GET PAYMENTS BY CHANNEL
    // =========================================
    public function getPaymentsByChannel(
        string $channel = 'Admin', 
        string $status = 'Pending', 
        int $page = 1, 
        int $limit = 20
    ): ?array {

        $offset = ($page - 1) * $limit;

        // Fetch payments with user & receipt details
        $sql = "
            SELECT 
                mp.payment_id,
                mp.payment_amount,
                mp.payment_status,
                mp.payment_ref,
                mp.created_at,
                mp.payment_channel,

                u.user_id,
                u.fullname,
                u.email,
                u.contact,

                mpr.receipt_ref,
                mpr.receipt_name

            FROM membership_payment mp
            INNER JOIN users u ON mp.user_id = u.user_id
            LEFT JOIN membership_payment_receipts mpr ON mp.payment_ref = mpr.receipt_ref
            WHERE mp.payment_status = ?
            AND mp.payment_channel = ?
            ORDER BY mp.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $payments = $this->queryAll($sql, [$status, $channel]);

        $total = $this->fetchColumn(
            "SELECT COUNT(*) FROM membership_payment WHERE payment_status = ? AND payment_channel = ?", 
            [$status, $channel]
        );

        $sum = $this->fetchColumn(
            "SELECT SUM(payment_amount) FROM membership_payment WHERE payment_status = ? AND payment_channel = ?", 
            [$status, $channel]
        );

        return $this->format($payments, $total, $sum, $page, $limit);
    }

    // =================================================================================
    // FETCH PERIODIC PAYMENT & REVENUE
    //      Fetch sales and revenue for both vendors and admins
    //      For each vendor, it also genertes store-wide sales and revenue summary
    //      This is useful for a dashboard usage
    // =================================================================================
    public function getPaymentAndRevenueByPeriod(
        string $view = 'admin', 
        ?int $userId = null, 
        ?string $period = 'today', 
        ?string $startDate = null, 
        ?string $endDate = null
    ): array {

        $baseCondition = "payment_status = 'Completed'";
        $conditions = [];
        $params = [];

        // Role-based filtering
        if ($view === 'admin') {
            $conditions[] = '1'; // no restriction
        } elseif ($view === 'user' && $userId !== null) {
            $conditions[] = 'user_id IN (SELECT user_id FROM membership_payment_backup WHERE user_id = ?)';
            $params[] = $userId;
        }

        // Date filtering
        switch ($period) {
            case 'today':
                $conditions[] = 'DATE(created_at) = CURDATE()';
                break;
            case 'yesterday':
                $conditions[] = 'DATE(created_at) = CURDATE() - INTERVAL 1 DAY';
                break;
            case 'last_week':
                $conditions[] = 'YEARWEEK(created_at, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)';
                break;
            case 'last_month':
                $conditions[] = 'YEAR(created_at) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(created_at) = MONTH(CURDATE() - INTERVAL 1 MONTH)';
                break;
            case 'last_year':
                $conditions[] = 'YEAR(created_at) = YEAR(CURDATE() - INTERVAL 1 YEAR)';
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $conditions[] = 'DATE(created_at) BETWEEN ? AND ?';
                    $params[] = $startDate;
                    $params[] = $endDate;
                } else {
                    throw new InvalidArgumentException('Custom range requires start_date and end_date');
                }
                break;
        }

        // Merge conditions
        $allConditions = array_merge([$baseCondition], $conditions);

        // Build query
        $sql = "SELECT 
                COUNT(DISTINCT payment_id) AS total_payments,
                SUM(payment_amount) AS total_revenue
            FROM membership_payment_backup
            WHERE " . implode(' AND ', $allConditions);

        // Execute
        $result = $this->queryOne($sql, $params);

        // Return clean values
        return [
            'total_payments' => (int)($result['total_payments'] ?? 0),
            'total_revenue'  => (float)($result['total_revenue'] ?? 0)
        ];
    }

    // =========================================
    // RESULT PAGINATION HELPER
    // =========================================
    private function format(
        array $data, 
        int $total, 
        int $sum, 
        int $page, 
        int $limit
    ): ?array {

        return [
            'payments'    => $data,
            'total'       => $total,
            'sum'         => $sum,
            'page'        => $page,
            'per_page'    => $limit,
            'total_pages' => ceil($total / $limit),
        ];
    }
}
