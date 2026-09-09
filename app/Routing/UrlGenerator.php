<?php

declare(strict_types=1);

namespace App\Routing;

use RuntimeException;

class UrlGenerator
{
    public function __construct(
        protected Router $router
    ) {}

    /**
     * Generate a URL from a named route.
     *
     * Example:
     *
     * route('profile.show', [
     *     'username' => 'john'
     * ]);
     *
     * Result:
     *
     * /u/john
     */
    public function route(
        string $name,
        array $parameters = []
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Resolve Route
        |--------------------------------------------------------------------------
        */

        $route = $this->router->getRouteByName($name);

        if (!$route instanceof Route) {
            throw new RuntimeException(
                "Route [{$name}] is not defined."
            );
        }

        $path = $route->path();

        /*
        |--------------------------------------------------------------------------
        | Find Route Parameters
        |--------------------------------------------------------------------------
        */

        preg_match_all(
            '/\{([\w]+)\}/',
            $path,
            $matches
        );

        $routeParameters = $matches[1] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Replace Route Parameters
        |--------------------------------------------------------------------------
        */

        foreach ($routeParameters as $parameter) {

            if (!array_key_exists($parameter, $parameters)) {

                throw new RuntimeException(
                    "Missing route parameter [{$parameter}] " .
                    "for route [{$name}]."
                );
            }

            $value = $parameters[$parameter];

            /*
            |--------------------------------------------------------------------------
            | Model / Object Route Key
            |--------------------------------------------------------------------------
            |
            | Allows objects to define their own URL identifier.
            |
            | Example:
            |
            | route('jobs.show', ['job' => $job])
            |
            | If $job->getRouteKey() returns "senior-php-developer",
            | that value will be used in the URL.
            |
            */

            if (
                is_object($value) &&
                method_exists($value, 'getRouteKey')
            ) {
                $value = $value->getRouteKey();
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Parameter Value
            |--------------------------------------------------------------------------
            */

            if (
                !is_scalar($value) &&
                !($value instanceof \Stringable)
            ) {
                throw new RuntimeException(
                    "Invalid value for route parameter " .
                    "[{$parameter}] on route [{$name}]."
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Encode Parameter
            |--------------------------------------------------------------------------
            */

            $value = rawurlencode((string) $value);

            /*
            |--------------------------------------------------------------------------
            | Replace Placeholder
            |--------------------------------------------------------------------------
            */

            $path = str_replace(
                '{' . $parameter . '}',
                $value,
                $path
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Build Application URL
        |--------------------------------------------------------------------------
        */

        $basePath = rtrim(
            config('app.base_path', ''),
            '/'
        );

        return $basePath . '/' . ltrim($path, '/');
    }
}
