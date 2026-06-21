<?php

namespace App\Models;

use PDO;

class Push extends Model
{
    protected string $table = 'push_tokens';

    /**
     * -----------------------------------------
     * ALLOWED USER TYPES
     * -----------------------------------------
     */
    protected array $allowedUserTypes = [
        'Admin',
        'User',
        'Leader',
        'Worker',
        'Dermatologist',
        'Doctor',
        'Freelancer',
        'Lawyer',
        'Therapist',
        'Vendor'
    ];

    // =====================================================
    // GET PUSH TOKENS
    // =====================================================
    public function getTokens(
        string $targetType = 'All',
        ?int $targetId = null
    ): array {

        $targetType = trim(ucwords($targetType));

        /**
         * -----------------------------------------
         * FETCH ALL ACTIVE TOKENS
         * -----------------------------------------
         */
        if ($targetType === 'All') {

            $stmt = $this->db->prepare(
                "SELECT token
                FROM {$this->table}
                WHERE is_active = 1"
            );

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        /**
         * -----------------------------------------
         * DETECT SINGLE TARGET MODE
         * Example:
         * Single Admin
         * Single User
         * -----------------------------------------
         */
        // $isSingle = strpos($targetType, 'Single ') === 0; --- IGNORE (For older PHP versions) ---

        $isSingle = str_starts_with(
            $targetType,
            'Single '
        );

        $userType = $isSingle
            ? str_replace('Single ', '', $targetType)
            : $targetType;

        /**
         * -----------------------------------------
         * VALIDATE USER TYPE
         * -----------------------------------------
         */
        if (!in_array($userType, $this->allowedUserTypes)) {
            return [];
        }

        /**
         * -----------------------------------------
         * FETCH SINGLE USER TOKENS
         * -----------------------------------------
         */
        if ($isSingle) {

            if ($targetId === null) {
                return [];
            }

            $stmt = $this->db->prepare(
                "SELECT token
                FROM {$this->table}
                WHERE user_type = ?
                AND user_id = ?
                AND is_active = 1"
            );

            $stmt->execute([
                $userType,
                $targetId
            ]);

            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        /**
         * -----------------------------------------
         * FETCH TOKENS BY ROLE
         * -----------------------------------------
         */
        $stmt = $this->db->prepare(
            "SELECT token
            FROM {$this->table}
            WHERE user_type = ?
            AND is_active = 1"
        );

        $stmt->execute([$userType]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // =====================================================
    // SAVE OR REACTIVATE TOKEN
    // =====================================================
    public function saveToken(
        string $token,
        string $deviceId,
        int $userId,
        string $userType
    ): bool {

        /**
         * -----------------------------------------
         * VALIDATE TOKEN
         * -----------------------------------------
         */
        if (empty(trim($token))) {
            return false;
        }

        /**
         * -----------------------------------------
         * VALIDATE DEVICE ID
         * -----------------------------------------
         */
        if (empty(trim($deviceId))) {
            return false;
        }

        /**
         * -----------------------------------------
         * VALIDATE USER TYPE
         * -----------------------------------------
         */
        $userType = trim(ucwords($userType));

        if (!in_array($userType, $this->allowedUserTypes)) {
            return false;
        }

        /**
         * -----------------------------------------
         * UPSERT TOKEN
         * -----------------------------------------
         */
        return $this->executeQuery(
            "
            INSERT INTO {$this->table}
            (
                token,
                device_id,
                user_id,
                user_type,
                is_active,
                last_seen
            )
            VALUES (?, ?, ?, ?, 1, NOW())

            ON DUPLICATE KEY UPDATE

                token      = VALUES(token),
                user_id    = VALUES(user_id),
                user_type  = VALUES(user_type),
                is_active  = 1,
                last_seen  = NOW()
            ",
            [
                $token,
                $deviceId,
                $userId,
                $userType
            ]
        );
    }

    // =====================================================
    // DEACTIVATE TOKEN
    // =====================================================
    public function deactivateToken(
        string $token,
        ?string $deviceId = null
    ): bool {

        if (empty(trim($token))) {
            return false;
        }

        $query = $this->query()
            ->where('token', '=', $token);

        /**
         * -----------------------------------------
         * OPTIONAL DEVICE FILTER
         * -----------------------------------------
         */
        if (!empty($deviceId)) {

            $query->where(
                'device_id',
                '=',
                $deviceId
            );
        }

        /**
         * -----------------------------------------
         * UPDATE TOKEN STATUS
         * -----------------------------------------
         */
        return $query->update([
            'is_active' => 0,
            'last_seen' => date('Y-m-d H:i:s')
        ]);
    }

    // =====================================================
    // DELETE DEAD TOKEN
    // =====================================================
    public function deleteToken(
        string $token
    ): bool {

        if (empty(trim($token))) {
            return false;
        }

        return $this->query()
            ->where('token', '=', $token)
            ->delete();
    }

    // =====================================================
    // CLEANUP OLD INACTIVE TOKENS
    // =====================================================
    public function cleanupInactiveTokens(
        int $days = 30
    ): bool {

        $days = max(1, $days);

        return $this->executeQuery(
            "
            DELETE FROM {$this->table}
            WHERE is_active = 0
            AND last_seen < DATE_SUB(NOW(), INTERVAL ? DAY)
            ",
            [$days]
        );
    }
}