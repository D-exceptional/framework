<?php
    namespace App\Http\Middlewares;

    use App\Http\Request;
    use App\Contracts\SessionInterface;
    use App\Exceptions\MiddlewareException;

    class AuthMiddleware
    {
        protected SessionInterface $session;

        public function __construct(SessionInterface $session)
        {
            $this->session = $session;
        }

        // =========================================
        // HANDLE AUTH CHECK
        // =========================================
        public function handle(
            Request $request, 
            callable $next, 
            array $config = []
        ) {
            // Check login
            if (!$this->session->validate()) {
                throw new MiddlewareException('Unauthorized', 401, 'redirect', '/login');
            }

            // Role check (if provided)
            if (isset($config['role'])) {
                $role = $this->session->role();

                $allowed = (array) $config['role'];

                if (!in_array($role, $allowed, true)) {
                    throw new MiddlewareException('Access denied', 403, 'json', '/login');
                }
            }

            // User is authenticated (and authorized if role check was done), continue to next middleware or controller
            $user = $this->session->user();
            if ($user) {
                $request->setUser($user);
            }

            // Continue pipeline
            return $next($request);
        }
    }
