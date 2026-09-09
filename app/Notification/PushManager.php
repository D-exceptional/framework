<?php

declare(strict_types=1);

namespace App\Notification;

use App\Models\Push;

/**
 * PushNotification
 *
 * Responsibilities:
 * - Send FCM push notifications
 * - Handle multi-device tokens safely
 * - Deactivate dead tokens conservatively
 * - Cache and refresh OAuth tokens
 */
class PushManager
{
    private string $serviceKeyPath;
    private string $cacheFile;
    private string $projectId;

    public function __construct(
        protected Push $pushModel
    ) {
        $this->serviceKeyPath = config('app.base_path', '') . '/storage/firebase-key.json';
        $this->cacheFile      = config('app.base_path', '') . '/storage/fcm-token-cache.json';
        $this->projectId      = config('firebase.project_id', '');
    }

    // =====================================================
    // BASE64 URL SAFE ENCODER (JWT)
    // ===================================================== 
    private function base64UrlEncode(
        string $data
    ): string {

        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // =====================================================
    // ACCESS TOKEN (CACHED)
    // ===================================================== 
    private function getAccessToken(): string
    {
        if (file_exists($this->cacheFile)) {
            $cached = json_decode(file_get_contents($this->cacheFile), true);

            if (
                isset($cached['access_token'], $cached['expires_at']) &&
                time() < ($cached['expires_at'] - 60)
            ) {
                return $cached['access_token'];
            }
        }

        return $this->generateNewToken();
    }

    // =====================================================
    // GENERATE NEW OAUTH TOKEN
    // ===================================================== 
    private function generateNewToken(): string
    {
        $key = json_decode(file_get_contents($this->serviceKeyPath), true);

        if (!$key || !isset($key['private_key'], $key['client_email'], $key['token_uri'])) {
            throw new \RuntimeException('Invalid Firebase service account key');
        }

        $now = time();

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ]));

        $payload = $this->base64UrlEncode(json_encode([
            'iss'   => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => $key['token_uri'],
            'iat'   => $now,
            'exp'   => $now + 3600
        ]));

        openssl_sign(
            "{$header}.{$payload}",
            $signature,
            $key['private_key'],
            OPENSSL_ALGO_SHA256
        );

        $jwt = "{$header}.{$payload}." . $this->base64UrlEncode($signature);

        $ch = curl_init($key['token_uri']);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt
            ])
        ]);

        $raw = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($raw, true) ?? [];

        if (!isset($response['access_token'], $response['expires_in'])) {
            throw new \RuntimeException('Failed to obtain FCM access token');
        }

        file_put_contents(
            $this->cacheFile,
            json_encode([
                'access_token' => $response['access_token'],
                'expires_at'   => time() + $response['expires_in']
            ]),
            LOCK_EX
        );

        return $response['access_token'];
    }

    // =====================================================
    // SEND PUSH NOTIFICATIONS
    // ===================================================== 
    public function sendPushNotification(
        string $target,
        ?int $userId,
        string $title,
        string $body,
        array $data = []
    ): void {

        $accessToken = $this->getAccessToken();
        $tokens      = $this->pushModel->getTokens($target, $userId);

        if (empty($tokens)) {
            throw new \RuntimeException('No tokens supplied for push notifications.');
        }

        $endpoint = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $headers  = [
            "Authorization: Bearer {$accessToken}",
            "Content-Type: application/json"
        ];

        foreach ($tokens as $token) {

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body
                    ],
                    'data' => array_merge([
                        'click_action' => $data['url'] ?? 'https://shop.mrsamase.com',
                        'type'         => $data['type'] ?? 'general',
                        'icon'         => 'https://shop.mrsamase.com/assets/img/logo-192.png'
                    ], $data)
                ]
            ];

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => json_encode($payload)
            ]);

            $rawResponse = curl_exec($ch);
            $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($rawResponse === false) {
                continue;
            }

            $response = json_decode($rawResponse, true) ?? [];

            if ($httpCode === 401) {
                @unlink($this->cacheFile);
                break;
            }

            if (isset($response['error']['status'])) {

                if ($response['error']['status'] === 'UNREGISTERED') {
                    // Delete token safely (token-based, not user-based)
                    $this->pushModel->deleteToken($token);
                }

                if ($response['error']['status'] === 'PERMISSION_DENIED') {
                    // Deactivate token safely (token-based, not user-based)
                    $this->pushModel->deactivateToken($token);
                }

                $this->writeLog(
                    "Push error [{$response['error']['status']}] for token " .
                    substr(hash('sha256', $token), 0, 12) .
                    ": " . ($response['error']['message'] ?? 'Unknown error')
                );
            }

            usleep(100000); // gentle send delay
        }
    }

    // =====================================================
    // LOG PUSH ERRORS
    // ===================================================== 
    private function writeLog(
        mixed $data
    ): void {

        $timestamp = date('Y-m-d H:i:s');

        $message = is_array($data)
            ? json_encode($data, JSON_PRETTY_PRINT)
            : (string) $data;

        $logFile = dirname(__DIR__, 2)
            . '/storage/logs/push-notification.log';

        $directory = dirname($logFile);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $result = file_put_contents(
            $logFile,
            "[{$timestamp}] {$message}" . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($result === false) {
            throw new \RuntimeException(
                "Unable to write push notification log: {$logFile}"
            );
        }
    }
}
