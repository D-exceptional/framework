<?php

declare(strict_types=1);

namespace App\Core;

use App\Http\Request;
use App\Http\Response;
use App\Routing\Router;

class Kernel
{
    public function __construct(
        protected Router $router
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request
    ): Response {

        $controllerResponse = $this->router->dispatch(
            $request
        );

        return $this->normalizeResponse(
            $controllerResponse
        );
    }

    /**
     * Normalize controller return values.
     */
    private function normalizeResponse(
        mixed $response
    ): Response {

        if (!$response instanceof Response) {
            throw new \RuntimeException(
                'Controllers must return a Response instance.'
            );
        }

        return $response;
    }
}