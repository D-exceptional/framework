<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Contracts\SessionInterface;

class SessionMiddleware
{
    public function __construct(
        protected SessionInterface $session
    ) {}

    public function handle(
        Request $request,
        callable $next,
        array $config = []
    ) {
        if ($this->session->validate()) {

            $user = $this->session->user();

            // User is authenticated 
            // Set the authenticated user in the request object
            // Continue to next middleware or controller
            
            if ($user) {
                $request->setUser($user);
            }
        }

        return $next($request);
    }
}