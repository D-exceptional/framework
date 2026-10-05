<?php

declare(strict_types=1);

namespace App\Routing;

use App\Core\Container;
use App\Exceptions\DuplicateRouteException;
use App\Exceptions\DuplicateRouteNameException;
use App\Exceptions\InvalidRoutePatternException;
use App\Exceptions\MethodNotAllowedException;
use App\Exceptions\RouteNameNotFoundException;
use App\Exceptions\RouteNotFoundException;
use App\Routing\Route;
use App\Http\Request;
use App\Http\Response;

class Router
{
    /**
     * Registered route collections.
     */
    protected array $collections = [];

    /**
     * Current active collection.
     */
    protected string $activeCollection = 'web';

    /**
     * General group prefix
     */
    protected string $groupPrefix = '';

    /**
     * General group middlewares
     */
    protected array $groupMiddlewares = [];

    /**
     * General named routes
     */
    protected array $namedRoutes = [];

    // =========================================
    // CONSTRUCTOR
    // =========================================

    public function __construct(
        protected Container $container
    ) {}


    // =========================================
    // COLLECTION MANAGEMENT
    // =========================================

    /**
     * Initialize a route collection if it doesn't exist.
     */
    private function initializeCollection(
        string $name
    ): void {

        if (!isset($this->collections[$name])) {
            $this->collections[$name] = [
                'static' => [],
                'dynamic' => [],
            ];
        }
    }

    /**
     * Determine which route collection should handle the request.
     */
    private function resolveCollection(
        string $uri
    ): string {

        $firstSegment = explode('/', trim($uri, '/'))[0] ?? '';

        if (isset($this->collections[$firstSegment])) {
            return $firstSegment;
        }

        return 'web';
    }

    /**
     * Load routes based on appropriate collections
     */
    public function loadCollection(
        string $name, 
        string $file
    ): void {

        $this->initializeCollection($name);

        $previousCollection = $this->activeCollection;

        try {
            $this->activeCollection = $name;

            $router = $this;

            require $file;
        } finally {
            $this->activeCollection = $previousCollection;
        }
    }

    // =========================================
    // GET ALL REGISTERED ROUTES
    // =========================================
    public function getRoutes(): array
    {
        return $this->collections;
    }

    // =========================================
    // NAMED ROUTES
    // =========================================
    private function registerNamedRoute(
        Route $route
    ): void {

        $name = $route->name();

        if ($name === null) {
            return;
        }

        if (isset($this->namedRoutes[$name])) {

            throw new DuplicateRouteNameException(
                "Duplicate route name: {$name}"
            );

        }

        $this->namedRoutes[$name] = $route;
    }

    public function getRouteByName(
        string $name
    ): Route {

        if (isset($this->namedRoutes[$name])) {
            return $this->namedRoutes[$name];
        }

        throw new RouteNameNotFoundException(
            "Route [{$name}] not found."
        );
    }


    // =========================================
    // ROUTE CACHE / HYDRATION
    // =========================================

    public function hydrateRoutes(
        array $routes
    ): void {

        $this->collections = [];
        $this->namedRoutes = [];

        foreach ($routes as $collection => $routeSet) {

            $this->collections[$collection] = [
                'static' => [],
                'dynamic' => [],
            ];

            // -----------------------------------------
            // Static Routes
            // -----------------------------------------

            foreach (
                $routeSet['static'] ?? [] as $method => $paths
            ) {
                foreach ($paths as $path => $routeData) {
                    $route = Route::toObject($routeData);

                    $this->collections[$collection]['static']
                        [$method][$path] = $route;

                    $this->registerNamedRoute($route);
                }
            }

            // -----------------------------------------
            // Dynamic Routes
            // -----------------------------------------

            foreach (
                $routeSet['dynamic'] ?? [] as $method => $groups
            ) {
                foreach ($groups as $group => $list) {

                    foreach ($list as $routeData) {

                        $route = Route::toObject($routeData);

                        $this->collections[$collection]['dynamic']
                            [$method][$group][] = $route;

                        $this->registerNamedRoute($route);
                    }
                }
            }
        }
    }

    public function cacheRoutes(): array
    {
        $cached = [];

        foreach (
            $this->collections as $collection => $routeSet
        ) {
            $cached[$collection] = [
                'static' => [],
                'dynamic' => [],
            ];

            // -----------------------------------------
            // Static Routes
            // -----------------------------------------

            foreach (
                $routeSet['static'] ?? [] as $method => $routes
            ) {
                foreach ($routes as $path => $route) {
                    $cached[$collection]['static']
                        [$method][$path] = $route->toArray();
                }
            }

            // -----------------------------------------
            // Dynamic Routes
            // -----------------------------------------

            foreach (
                $routeSet['dynamic'] ?? [] as $method => $groups
            ) {
                foreach ($groups as $group => $routes) {
                    foreach ($routes as $route) {
                        $cached[$collection]['dynamic']
                            [$method][$group][] = $route->toArray();
                    }
                }
            }
        }

        return $cached;
    }


    // =========================================
    // ROUTE GROUPING
    // =========================================

    private function extractGroup(
        string $path
    ): string {

        $segments = explode(
            '/',
            trim($path, '/')
        );

        if (($segments[0] ?? '') === 'api') {
            return isset($segments[1])
                ? "api/{$segments[1]}"
                : 'api/*';
        }

        return $segments[0] ?? '*';
    }

    public function group(
        string $prefix,
        callable $callback,
        array $middlewares = []
    ): void {

        $previousPrefix = $this->groupPrefix;

        $previousMiddlewares =
            $this->groupMiddlewares;

        $this->groupPrefix = rtrim(
            $previousPrefix
            . '/'
            . trim($prefix, '/'),
            '/'
        );

        $this->groupMiddlewares = array_merge(
            $previousMiddlewares,
            $middlewares
        );

        try {
            $callback($this);
        } finally {
            $this->groupPrefix = $previousPrefix;

            $this->groupMiddlewares =
                $previousMiddlewares;
        }
    }


    // =========================================
    // ROUTE REGISTRATION
    // =========================================

    public function add(
        string $method,
        string $path,
        string $controller,
        string $action,
        array $middlewares = [],
        ?string $name = null
    ): Route {

        $fullPath = rtrim(
            $this->groupPrefix
            . '/'
            . ltrim($path, '/'),
            '/'
        );

        $fullPath = $fullPath ?: '/';

        $middlewares = array_merge(
            $this->groupMiddlewares,
            $middlewares
        );

        $method = strtoupper($method);

        $this->initializeCollection(
            $this->activeCollection
        );

        $route = new Route(
            method: $method,
            path: $fullPath,
            controller: $controller,
            action: $action,
            middlewares: $middlewares,
            name: $name
        );

        $this->registerNamedRoute($route);


        // -----------------------------------------
        // Dynamic Route
        // -----------------------------------------

        if (str_contains($fullPath, '{')) {

            $pattern = preg_replace_callback(
                '/\{([\w]+)\}/',
                fn ($match) =>
                    '(?P<' . $match[1] . '>[^/]+)',
                $fullPath
            );

            $pattern = "#^{$pattern}$#";

            if (@preg_match($pattern, '') === false) {
                throw new InvalidRoutePatternException(
                    "Invalid route pattern: {$fullPath}"
                );
            }

            $route->setPattern($pattern);

            $group = $this->extractGroup(
                $fullPath
            );

            $dynamicRoutes =
                &$this->collections[
                    $this->activeCollection
                ]['dynamic'][$method][$group];

            foreach ($dynamicRoutes ?? [] as $existingRoute) {
                if ($existingRoute->path() === $fullPath) {
                    throw new DuplicateRouteException(
                        "Duplicate route: "
                        . "{$method} {$fullPath}"
                    );
                }
            }

            $dynamicRoutes[] = $route;

            return $route;
        }


        // -----------------------------------------
        // Static Route
        // -----------------------------------------

        if (
            isset(
                $this->collections[
                    $this->activeCollection
                ]['static'][$method][$fullPath]
            )
        ) {
            throw new DuplicateRouteException(
                "Duplicate route: "
                . "{$method} {$fullPath}"
            );
        }

        $this->collections[
            $this->activeCollection
        ]['static'][$method][$fullPath] = $route;

        return $route;
    }


    // =========================================
    // HTTP VERB HELPERS
    // =========================================

    public function get(
        string $path,
        array $handler,
        array $middlewares = [],
        ?string $name = null
    ): Route {

        return $this->add(
            'GET',
            $path,
            $handler[0],
            $handler[1],
            $middlewares,
            $name
        );
    }

    public function post(
        string $path,
        array $handler,
        array $middlewares = [],
        ?string $name = null
    ): Route {

        return $this->add(
            'POST',
            $path,
            $handler[0],
            $handler[1],
            $middlewares,
            $name
        );
    }

    public function put(
        string $path,
        array $handler,
        array $middlewares = [],
        ?string $name = null
    ): Route {

        return $this->add(
            'PUT',
            $path,
            $handler[0],
            $handler[1],
            $middlewares,
            $name
        );
    }

    public function patch(
        string $path,
        array $handler,
        array $middlewares = [],
        ?string $name = null
    ): Route {

        return $this->add(
            'PATCH',
            $path,
            $handler[0],
            $handler[1],
            $middlewares,
            $name
        );
    }

    public function delete(
        string $path,
        array $handler,
        array $middlewares = [],
        ?string $name = null
    ): Route {

        return $this->add(
            'DELETE',
            $path,
            $handler[0],
            $handler[1],
            $middlewares,
            $name
        );
    }


    // =========================================
    // ROUTE EXECUTION
    // =========================================

    private function runRoute(
        Route $route,
        array $params,
        Request $request
    ): Response {

        $controller = $this->container->get(
            $route->controller()
        );

        $action = $route->action();

        $request->setRouteParams($params);

        $next = function (
            Request $request
        ) use (
            $controller,
            $action,
            $params
        ): Response {

            return $this->container->call(
                [$controller, $action],
                [
                    'request' => $request,
                    ...$params,
                ]
            );
        };

        foreach (
            array_reverse($route->middlewares())
            as $middlewareDef
        ) {
            $next = function (
                Request $request
            ) use (
                $middlewareDef,
                $next
            ): Response {
                [
                    $class,
                    $method,
                    $config
                ] = array_pad(
                    $middlewareDef,
                    3,
                    []
                );

                $middleware = $this->container->get(
                    $class
                );

                return $this->container->call(
                    [$middleware, $method],
                    [
                        'request' => $request,
                        'next' => $next,
                        'config' => $config,
                    ]
                );
            };
        }

        return $next($request);
    }


    // =========================================
    // ROUTE DISPATCHING
    // =========================================

    public function dispatch(
        Request $request
    ): Response {

        $uri    = $request->uri();
        $method = $request->method();

        $collection = $this->resolveCollection(
            $uri
        );

        $routes = $this->collections[
            $collection
        ];


        // -----------------------------------------
        // Static Route
        // -----------------------------------------

        if (
            isset(
                $routes['static'][$method][$uri]
            )
        ) {
            return $this->runRoute(
                $routes['static'][$method][$uri],
                [],
                $request
            );
        }


        // -----------------------------------------
        // Dynamic Routes
        // -----------------------------------------

        $group = $this->extractGroup($uri);

        $candidates =
            $routes['dynamic'][$method][$group]
            ?? [];

        // Leading dynamic route fallback
        if ($group !== '*') {
            $candidates = array_merge(
                $candidates,
                $routes['dynamic'][$method]['*']
                ?? []
            );
        }

        // API leading dynamic route fallback
        if (
            str_starts_with($group, 'api/')
            && $group !== 'api/*'
        ) {
            $candidates = array_merge(
                $candidates,
                $routes['dynamic'][$method]['api/*']
                ?? []
            );
        }

        foreach ($candidates as $route) {
            $pattern = $route->pattern();

            if (
                $pattern !== null
                && preg_match(
                    $pattern,
                    $uri,
                    $matches
                ) === 1
            ) {
                $params = array_filter(
                    $matches,
                    'is_string',
                    ARRAY_FILTER_USE_KEY
                );

                return $this->runRoute(
                    $route,
                    $params,
                    $request
                );
            }
        }


        // -----------------------------------------
        // Method Not Allowed
        // -----------------------------------------

        $allowed = $this->findAllowedMethods(
            $uri,
            $routes
        );

        if (!empty($allowed)) {
            throw new MethodNotAllowedException(
                "Method {$method} not allowed "
                . "for {$uri}. Allowed: "
                . implode(', ', $allowed),
                405
            );
        }


        // -----------------------------------------
        // Route Not Found
        // -----------------------------------------

        if (
            (env('APP_DEBUG') ?? 'false')
            === 'true'
        ) {
            $this->writeLog(
                "Route not found: {$uri}"
            );
        }

        throw new RouteNotFoundException(
            "Route not found: {$uri}",
            404
        );
    }


    // =========================================
    // ALLOWED METHODS
    // =========================================

    private function findAllowedMethods(
        string $uri,
        array $routes
    ): array {

        $methods = [];

        // -----------------------------------------
        // Static Routes
        // -----------------------------------------

        foreach (
            $routes['static'] ?? []
            as $method => $map
        ) {
            if (isset($map[$uri])) {
                $methods[] = $method;
            }
        }


        // -----------------------------------------
        // Dynamic Routes
        // -----------------------------------------

        $group = $this->extractGroup($uri);

        foreach (
            $routes['dynamic'] ?? []
            as $method => $groups
        ) {
            $candidateGroups = [$group];

            if ($group !== '*') {
                $candidateGroups[] = '*';
            }

            if (
                str_starts_with($group, 'api/')
                && $group !== 'api/*'
            ) {
                $candidateGroups[] = 'api/*';
            }

            foreach (
                array_unique($candidateGroups)
                as $candidateGroup
            ) {
                foreach (
                    $groups[$candidateGroup] ?? []
                    as $route
                ) {
                    $pattern = $route->pattern();

                    if (
                        $pattern !== null
                        && preg_match(
                            $pattern,
                            $uri
                        ) === 1
                    ) {
                        $methods[] = $method;
                    }
                }
            }
        }

        return array_values(
            array_unique($methods)
        );
    }


    // =========================================
    // DEBUG LOGGING
    // =========================================

    private function writeLog(
        mixed $data
    ): void {

        $timestamp = date(
            'Y-m-d H:i:s'
        );

        $message = is_array($data)
            ? json_encode(
                $data,
                JSON_PRETTY_PRINT
            )
            : (string) $data;

        $logFile = dirname(
            __DIR__,
            2
        ) . '/storage/logs/router.log';

        if (!is_dir(dirname($logFile))) {
            mkdir(
                dirname($logFile),
                0755,
                true
            );
        }

        @file_put_contents(
            $logFile,
            "[{$timestamp}] {$message}"
            . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}

