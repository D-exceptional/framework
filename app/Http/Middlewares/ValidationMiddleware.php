<?php
    namespace App\Http\Middlewares;

    use App\Http\Request;
    use App\Validations\Validator;
    use App\Exceptions\MiddlewareException;

    class ValidationMiddleware
    {
        protected Validator $validator;

        public function __construct(Validator $validator)
        {
            $this->validator = $validator;
        }

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
                    400,
                    'json'
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