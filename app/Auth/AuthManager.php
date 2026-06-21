<?php

namespace App\Auth;

use App\Auth\JWT;

class AuthManager
{
    public function __construct(
       protected JWT $jwt
    ) {
        $this->jwt = $jwt;
    }

    public function boot(
        array $user
    ): void {
        // This method can be used to perform any additional login logic if needed
        // For JWT-based auth, the actual token generation is handled in the session driver
    }
}