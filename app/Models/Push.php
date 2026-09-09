<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Push extends Model
{
    protected string $table = 'push_tokens';

    public function getTokenIds(
        string $targetType = 'all', 
        ?int $targetId = null
    ): array {

        $targetType = ucwords($targetType);

        switch ($targetType) {

            case 'all':

                $stmt = $this->db->prepare("
                    SELECT 
                        token 
                    FROM {$table}
                    WHERE 
                        is_active = 1
                ");

                $stmt->execute();
                break;

            case 'admin':
            case 'customer':
            case 'vendor':

                $stmt = $this->db->prepare("
                    SELECT 
                        token 
                    FROM {$table}
                    WHERE 
                        user_type = ? 
                        AND is_active = 1
                ");

                $stmt->execute([$targetType]);
                break;

            case 'single admin':
            case 'single customer':
            case 'single vendor':

                if ($targetId === null) {
                    return [];
                }

                $userType = str_replace('single ', '', $targetType);

                $stmt = $this->db->prepare("
                    SELECT 
                        token 
                    FROM {$table}
                    WHERE 
                        user_type = ? 
                        AND user_id = ? 
                        AND is_active = 1
                ");

                $stmt->execute([$userType, $targetId]);
                break;

            default:
                return [];
        }

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function saveToken(
        string $token, 
        string $deviceId, 
        int $userId, 
        string $userType
    ): bool {

        $stmt = $this->db->prepare("
            INSERT INTO push_tokens (token, device_id, user_id, user_type, is_active, last_seen)
            VALUES (?, ?, ?, ?, 1, NOW())
            ON DUPLICATE KEY UPDATE
                token      = VALUES(token),
                is_active  = 1,
                last_seen  = NOW()
        ");

        return $stmt->execute([$token, $deviceId, $userId, $userType]);
    }

    public function deactivateToken(
        string $token, 
        ?string $deviceId = null
    ): bool {

        $sql = "
            UPDATE push_tokens
            SET 
                is_active = 0,
                last_seen = NOW()
            WHERE 
                token = ?
        ";

        $params = [$token];

        if ($deviceId !== null) {
            $sql .= " AND device_id = ?";
            $params[] = $deviceId;
        }

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function deleteToken(string $token): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM {$table}
            WHERE 
                token = ?
        ");

        $stmt->execute([$token]);
    }
}
