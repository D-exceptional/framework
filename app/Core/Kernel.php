<?php

declare(strict_types=1);

namespace App\Core;

use App\Http\Request;
use App\Http\Response;
use App\Http\ResponseEmitter;
use App\Routing\Router;

class Kernel
{
    public function __construct(
        protected Router $router,
        protected Response $response,
        protected ResponseEmitter $emitter
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request
    ): never {

        $controllerResponse = $this->router->dispatch(
            $request
        );

        $this->normalizeResponse(
            $controllerResponse
        );
    }

    /**
     * Normalize controller return values.
     */
    protected function normalizeResponse(
        mixed $response
    ): never {

        if (!$response instanceof Response) {
            throw new \RuntimeException(
                'Controllers must return a Response instance.'
            );
        }

        $this->emitter->emit(
            $response
        );
    }
}