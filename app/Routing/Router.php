<?php

declare(strict_types=1);

namespace App\Routing;

use App\Core\Container;
use App\Routing\Route;
use App\Http\Request;
use App\Exceptions\RouteNotFoundException;

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

    public function __construct(
        protected Container $container
    ) {}

    /**
     * Initialize a route collection if it doesn't exist.
     */
    private function initializeCollection(string $name): void
    {
        if (!isset($this->collections[$name])) {

            $this->collections[$name] = [
                'static' => [],
                'dynamic' => []
            ];
        }
    }

    /**
     * Set the active route collection.
     */
    public function setCollection(
        string $collection
    ): void {

        $this->activeCollection = $collection;
    }

    /**
     * Determine which route collection should handle the request.
     */
    private function resolveCollection(
        string $uri
    ): string {

        $segments = explode('/', trim($uri, '/'));

        $firstSegment = $segments[0] ?? '';

        $collection = in_array($firstSegment, ['api']) ? 'api' : 'web';

        return $collection;
    }

    /**
     * Reset the active collection tracker
     */
    public function resetCollection(): void
    {
        $this->activeCollection = 'web';
    }

    // =========================================
    // GET ALL REGISTERED ROUTES
    // =========================================
    public function getRoutes(): array
    {
        return $this->collections;
    }

    // =========================================
    // RESTORE CACHED ROUTES INTO ROUTE OBJECTS
    // =========================================
    public function setRoutes(
        array $routes
    ): void {

        $this->collections = [];

        foreach ($routes as $collection => $routeSet) {

            $this->collections[$collection] = [
                'static'  => [],
                'dynamic' => [],
            ];

            // =========================================
            // STATIC ROUTES
            // =========================================

            foreach ($routeSet['static'] ?? [] as $method => $routes) {

                foreach ($routes as $path => $routeData) {

                    $this->collections[$collection]['static']
                        [$method][$path] =
                        Route::toObject($routeData);
                }
            }

            // =========================================
            // DYNAMIC ROUTES
            // =========================================

            foreach ($routeSet['dynamic'] ?? [] as $method => $groups) {

                foreach ($groups as $group => $routes) {

                    foreach ($routes as $routeData) {

                        $this->collections[$collection]['dynamic']
                            [$method][$group][] =
                            Route::toObject($routeData);
                    }
                }
            }
        }
    }

    /**
     * Convert registered Route objects into cacheable arrays.
     */
    public function toCacheArray(): array
    {
        $cached = [];

        foreach ($this->collections as $collection => $routeSet) {

            $cached[$collection] = [
                'static'  => [],
                'dynamic' => [],
            ];

            // =========================================
            // STATIC ROUTES
            // =========================================

            foreach ($routeSet['static'] ?? [] as $method => $routes) {

                foreach ($routes as $path => $route) {

                    $cached[$collection]['static'][$method][$path]
                        = $route->toArray();
                }
            }

            // =========================================
            // DYNAMIC ROUTES
            // =========================================

            foreach ($routeSet['dynamic'] ?? [] as $method => $groups) {

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

    /**
     * Find a registered route by name.
     */
    public function getRouteByName(
        string $name
    ): Route {

        foreach ($this->collections as $collection) {

            // =========================================
            // STATIC ROUTES
            // =========================================

            foreach ($collection['static'] ?? [] as $methodRoutes) {

                foreach ($methodRoutes as $route) {

                    if (
                        $route instanceof Route &&
                        $route->name() === $name
                    ) {
                        return $route;
                    }
                }
            }

            // =========================================
            // DYNAMIC ROUTES
            // =========================================

            foreach ($collection['dynamic'] ?? [] as $methodRoutes) {

                foreach ($methodRoutes as $groupRoutes) {

                    foreach ($groupRoutes as $route) {

                        if (
                            $route instanceof Route &&
                            $route->name() === $name
                        ) {
                            return $route;
                        }
                    }
                }
            }
        }

        throw new \RuntimeException(
            "Route [{$name}] not found."
        );
    }

    // =========================================================
    // EXTRACT GROUP NAME FROM PATH FOR DYNAMIC ROUTE GROUPING
    // =========================================================
    private function extractGroup(
        string $path
    ): string {

        $segments = explode('/', trim($path, '/'));

        if (isset($segments[0]) &&
            in_array($segments[0], ['api', 'admin'])) {

            return $segments[1] ?? 'root';
        }

        return $segments[0] ?? 'root';
    }
    
    // =========================================================
    // Add routes to router
    // =========================================================
    public function add(
        string $method,
        string $path,
        string $controller,
        string $action,
        array $middlewares = [],
        ?string $name = null
    ): void {

        $fullPath = rtrim(
            $this->groupPrefix . '/' . ltrim($path, '/'),
            '/'
        );

        $fullPath = $fullPath ?: '/';

        $middlewares = array_merge(
            $this->groupMiddlewares,
            $middlewares
        );

        $method = strtoupper($method);

        /*
        |--------------------------------------------------------------------------
        | Create Route
        |--------------------------------------------------------------------------
        */

        $route = new Route(
            method: $method,
            path: $fullPath,
            controller: $controller,
            action: $action,
            middlewares: $middlewares,
            name: $name
        );

        /*
        |--------------------------------------------------------------------------
        | Initialize Collection
        |--------------------------------------------------------------------------
        */

        $this->initializeCollection(
            $this->activeCollection
        );

        /*
        |--------------------------------------------------------------------------
        | Dynamic Route
        |--------------------------------------------------------------------------
        */

        if (preg_match('/\{[\w]+\}/', $fullPath)) {

            $pattern = preg_replace_callback(
                '/\{([\w]+)\}/',
                function ($matches) {
                    return '(?P<' . $matches[1] . '>[^/]+)';
                },
                $fullPath
            );

            $pattern = "#^" . $pattern . "$#";

            $route->setPattern($pattern);

            $group = $this->extractGroup(
                $fullPath
            );

            $this->collections[
                $this->activeCollection
            ]['dynamic'][$method][$group][] = $route;

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Static Route
        |--------------------------------------------------------------------------
        */

        $this->collections[
            $this->activeCollection
        ]['static'][$method][$fullPath] = $route;
    }

    // =========================================
    // ROUTE GROUP (FOR CLEANER API DESIGN)
    // =========================================
    public function group(
        string $prefix, 
        callable $callback, 
        array $middlewares = []
    ): void {

        $previousPrefix      = $this->groupPrefix;
        $previousMiddlewares = $this->groupMiddlewares;

        $this->groupPrefix      = rtrim($previousPrefix . '/' . trim($prefix, '/'), '/');
        $this->groupMiddlewares = array_merge($previousMiddlewares, $middlewares);

        $callback($this); // Pass router instance

        $this->groupPrefix      = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    // =========================================
    // REST GET METHOD
    // =========================================
    public function get(
        string $path, 
        array $handler, 
        array $middlewares = [],
        ?string $name = null
    ): void {

        $this->add('GET', $path, $handler[0], $handler[1], $middlewares, $name);
    }

    // =========================================
    // REST POST METHOD
    // =========================================
    public function post(
        string $path, 
        array $handler, 
        array $middlewares = [],
        ?string $name = null
    ): void {

        $this->add('POST', $path, $handler[0], $handler[1], $middlewares, $name);
    }

    // =========================================
    // REST PUT METHOD
    // =========================================
    public function put(
        string $path, 
        array $handler, 
        array $middlewares = [],
        ?string $name = null
    ): void {

        $this->add('PUT', $path, $handler[0], $handler[1], $middlewares, $name);
    }

    // =========================================
    // REST PATCH METHOD
    // =========================================
    public function patch(
        string $path, 
        array $handler, 
        array $middlewares = [],
        ?string $name = null
    ): void {

        $this->add('PATCH', $path, $handler[0], $handler[1], $middlewares, $name);
    }

    // =========================================
    // REST DELETE METHOD
    // =========================================
    public function delete(
        string $path, 
        array $handler, 
        array $middlewares = [],
        ?string $name = null
    ): void {

        $this->add('DELETE', $path, $handler[0], $handler[1], $middlewares, $name);
    }

    // =========================================
    // RUN ROUTE WITH MIDDLEWARE PIPELINE
    // =========================================
    private function runRoute(
        Route $route,
        array $params,
        Request $request
    ) {
        $controller = $this->container->get(
            $route->controller()
        );

        $action = $route->action();

        /*
        |--------------------------------------------------------------------------
        | Attach route parameters to request
        |--------------------------------------------------------------------------
        */

        $request->setRouteParams($params);

        /*
        |--------------------------------------------------------------------------
        | Controller
        |--------------------------------------------------------------------------
        */

        $next = function ($request) use (
            $controller,
            $action,
            $params
        ) {

            return $this->container->call(
                [$controller, $action],
                [
                    'request' => $request,
                    ...$params
                ]
            );
        };

        /*
        |--------------------------------------------------------------------------
        | Middleware Pipeline
        |--------------------------------------------------------------------------
        */

        foreach (
            array_reverse($route->middlewares())
            as $middlewareDef
        ) {

            $next = function ($request) use (
                $middlewareDef,
                $next
            ) {

                [$class, $method, $config] =
                    array_pad(
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
                        'next'    => $next,
                        'config'  => $config
                    ]
                );
            };
        }

        return $next($request);
    }

    // =========================================
    // DISPATCH INCOMING REQUEST
    // =========================================
    public function dispatch(Request $request)
    {
        $uri    = $request->uri();
        $method = $request->method();

        $collection = $this->resolveCollection($uri);

        $this->initializeCollection($collection);

        $routes = $this->collections[$collection];

        // -------------------------------
        // STATIC ROUTE LOOKUP
        // -------------------------------
        if (isset($routes['static'][$method][$uri])) {

            $route = $routes['static'][$method][$uri];

            return $this->runRoute($route, [], $request);
        }

        // -------------------------------
        // DYNAMIC ROUTES
        // -------------------------------
        $group = $this->extractGroup($uri);

        $dynamicRoutes = $routes['dynamic'][$method][$group] ?? [];

        foreach ($dynamicRoutes as $route) {

            $matches = [];

            if ($route->pattern() !== null &&
                preg_match($route->pattern(), $uri, $matches)) {

                $params = array_filter(
                    $matches,
                    'is_string',
                    ARRAY_FILTER_USE_KEY
                );

                return $this->runRoute($route, $params, $request);
            }
        }

        $this->writeLog("Route not found: $uri");

        throw new RouteNotFoundException("Route not found: $uri", 404);
    }

    // =========================================
    // LOG ERROR MESSAGES
    // =========================================
    private function writeLog(
        mixed $data
    ): void {

        $timestamp = date('Y-m-d H:i:s');

        $message = is_array($data)
            ? json_encode($data, JSON_PRETTY_PRINT)
            : (string) $data;

        $logFile = dirname(__DIR__, 2)
            . '/storage/logs/router-error.log';

        $directory = dirname($logFile);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $result = file_put_contents(
            $logFile,
            "[{$timestamp}] {$message}" . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($result === false) {
            throw new \RuntimeException(
                "Unable to write router log: {$logFile}"
            );
        }
    }
}
