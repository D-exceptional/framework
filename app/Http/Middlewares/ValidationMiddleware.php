<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Request;
use App\Validations\Validator;
use App\Exceptions\MiddlewareException;

class ValidationMiddleware
{
    public function __construct(
        protected Validator $validator
    ) {}

    // =========================================
    // HANDLE VALIDATIONS
    // =========================================
    public function handle(
        Request $request,
        callable $next,
        array $config = []
    ) {

        // Ensure validation rules exist
        if (!isset($config['rules'])) {

            throw new MiddlewareException(
                'Validation rules not provided',
                400
            );
        }

        // Get normalized request data
        $data = $request->all();

        // Run validation
        $this->validator->validate(
            $data,
            $config['rules']
        );

        // Continue middleware pipeline
        return $next($request);
    }
}