<?php

declare(strict_types=1);

namespace App\Auth;

use Exception;

class Jwt
{
    protected string $secret;
    protected string $algo = 'sha256';

    public function __construct()
    {
        $this->secret = config('jwt.secret'); 
    }

    /**
     * Generate JWT
     */
    public function generate(
        array $payload
    ): string {

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $headerEncoded = $this->base64UrlEncode(
            json_encode($header)
        );

        $payloadEncoded = $this->base64UrlEncode(
            json_encode($payload)
        );

        $signature = hash_hmac(
            $this->algo,
            "$headerEncoded.$payloadEncoded",
            $this->secret,
            true
        );

        $signatureEncoded = $this->base64UrlEncode(
            $signature
        );

        return implode('.', [
            $headerEncoded,
            $payloadEncoded,
            $signatureEncoded
        ]);
    }

    /**
     * Decode & verify JWT
     */
    public function verify(
        string $token
    ): array {

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception('Invalid token');
        }

        [$header, $payload, $signature] = $parts;

        $validSignature = $this->base64UrlEncode(
            hash_hmac(
                $this->algo,
                "$header.$payload",
                $this->secret,
                true
            )
        );

        if (!hash_equals($validSignature, $signature)) {
            throw new Exception('Invalid signature');
        }

        $payloadData = json_decode(
            $this->base64UrlDecode($payload),
            true
        );

        if (!$payloadData) {
            throw new Exception('Invalid payload');
        }

        // Check expiration
        if (
            isset($payloadData['exp']) &&
            time() > $payloadData['exp']
        ) {
            throw new Exception('Token expired');
        }

        return $payloadData;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(
            strtr(base64_encode($data), '+/', '-_'),
            '='
        );
    }

    protected function base64UrlDecode(string $data): string
    {
        return base64_decode(
            strtr($data, '-_', '+/')
        );
    }
}