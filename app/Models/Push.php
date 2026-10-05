<?php

declare(strict_types=1);

namespace App\Models;

class Push extends Model
{
    protected string $table = 'push_tokens';

    // =========================================
    // GET TOKEN IDS
    // =========================================

    public function getTokenIds(
        string $targetType = 'all',
        ?int $targetId = null
    ): array {

        $targetType = strtolower($targetType);

        switch ($targetType) {

            case 'all':

                return $this->table()
                    ->where('is_active', '=', 1)
                    ->pluck('token');

            case 'admin':
            case 'customer':
            case 'vendor':

                return $this->table()
                    ->where('user_type', '=', $targetType)
                    ->where('is_active', '=', 1)
                    ->pluck('token');

            case 'single admin':
            case 'single customer':
            case 'single vendor':

                if ($targetId === null) {
                    return [];
                }

                $userType = str_replace(
                    'single ',
                    '',
                    $targetType
                );

                return $this->table()
                    ->where('user_type', '=', $userType)
                    ->where('user_id', '=', $targetId)
                    ->where('is_active', '=', 1)
                    ->pluck('token');

            default:

                return [];
        }
    }

    // =========================================
    // SAVE TOKEN
    // =========================================

    public function saveToken(
        string $token,
        string $deviceId,
        int $userId,
        string $userType
    ): bool {

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table}
                (
                    token,
                    device_id,
                    user_id,
                    user_type,
                    is_active,
                    last_seen
                )
            VALUES
                (?, ?, ?, ?, 1, NOW())

            ON DUPLICATE KEY UPDATE
                token = VALUES(token),
                is_active = 1,
                last_seen = NOW()
        ");

        return $stmt->execute([
            $token,
            $deviceId,
            $userId,
            $userType,
        ]);
    }

    // =========================================
    // DEACTIVATE TOKEN
    // =========================================

    public function deactivateToken(
        string $token,
        ?string $deviceId = null
    ): bool {

        return $this->table()
            ->where('token', '=', $token)
            ->when(
                $deviceId !== null, 
                
                function ($query) use ($deviceId) {
                    $query->where('device_id', '=', $deviceId);
                }
            )
            ->update([
                'is_active' => 0,
                'last_seen' => date('Y-m-d H:i:s'),
            ]);
    }

    // =========================================
    // DELETE TOKEN
    // =========================================

    public function deleteToken(
        string $token
    ): bool {

        return $this->table()
            ->where('token', '=', $token)
            ->delete();
    }
}