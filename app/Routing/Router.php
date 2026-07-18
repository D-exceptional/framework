<?php
namespace App\Routing;

use App\Core\Application;
use App\Http\Request;
use App\Exceptions\MiddlewareException;
use App\Exceptions\RouteNotFoundException;

class Router
{
    protected Application $app;
    protected Container $container;


    protected array $routes = [
        'static'  => [],
        'dynamic' => []
    ];
    protected string $groupPrefix = '';
    protected array $groupMiddlewares = [];

    public function __construct(Application $app)
    {
        $this->container = $app->container();
    }

    // =========================================
    // GET ALL REGISTERED ROUTES
    // =========================================
    public function getRoutes(): array
    {
        return $this->routes;
    }

    // =========================================
    // SET ROUTES
    // =========================================
    public function setRoutes(
        array $routes
    ): void {

        $this->routes = $routes;
    }

    // =========================================================
    // EXTRACT GROUP NAME FROM PATH FOR DYNAMIC ROUTE GROUPING
    // =========================================================
    private function extractGroup(
        string $path
    ): string {

        $segments = explode('/', trim($path, '/'));

        return $segments[1] ?? 'root';
    }

    // =========================================
    // REGISTER A ROUTE
    // =========================================
    public function add(
        string $method, 
        string $path, 
        string $controller, 
        string $action, 
        array $middlewares = []
    ): void {

        $fullPath = rtrim($this->groupPrefix . '/' . ltrim($path, '/'), '/');
        $fullPath = $fullPath ?: '/';

        $middlewares = array_merge($this->groupMiddlewares, $middlewares);

        $route = [
            'method'      => strtoupper($method),
            'path'        => $fullPath,
            'controller'  => $controller,
            'action'      => $action,
            'middlewares' => $middlewares
        ];

        $method = strtoupper($method);

        // Detect dynamic route
        if (preg_match('/\{[\w]+\}/', $fullPath)) {

            $pattern = preg_replace_callback(
                '/\{([\w]+)\}/',
                function ($matches) {
                    return '(?P<' . $matches[1] . '>[^/]+)';
                },
                $fullPath
            );

            $pattern = "#^" . $pattern . "$#";

            $route['pattern'] = $pattern;

            $group = $this->extractGroup($fullPath);

            $this->routes['dynamic'][$method][$group][] = $route;

        } else {
            // Static route
            $this->routes['static'][$method][$fullPath] = $route;
        }
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
        array $middlewares = []
    ): void {

        $this->add('GET', $path, $handler[0], $handler[1], $middlewares);
    }

    // =========================================
    // REST POST METHOD
    // =========================================
    public function post(
        string $path, 
        array $handler, 
        array $middlewares = []
    ): void {

        $this->add('POST', $path, $handler[0], $handler[1], $middlewares);
    }

    // =========================================
    // REST PUT METHOD
    // =========================================
    public function put(
        string $path, 
        array $handler, 
        array $middlewares = []
    ): void {

        $this->add('PUT', $path, $handler[0], $handler[1], $middlewares);
    }

    // =========================================
    // REST DELETE METHOD
    // =========================================
    public function delete(
        string $path, 
        array $handler, 
        array $middlewares = []
    ): void {

        $this->add('DELETE', $path, $handler[0], $handler[1], $middlewares);
    }

    // =========================================
    // RUN ROUTE WITH MIDDLEWARE PIPELINE
    // =========================================
    private function runRoute(
        array $route, 
        array $params, 
        Request $request
    )
    {
        $controller = $this->container->get($route['controller']);
        $action     = $route['action'];

        // Attach route params to request object
        $request->setRouteParams($params);

        $middlewares = $route['middlewares'];

        $next = function ($request) use ($controller, $action) {
            return $controller->{$action}($request);
        };

        foreach (array_reverse($middlewares) as $middlewareDef) {

            $next = function ($request) use ($middlewareDef, $next) {

                [$class, $method, $config] = array_pad($middlewareDef, 3, []);

                $middleware = $this->container->get($class);

                return $middleware->{$method}(
                    $request,
                    $next,
                    $config
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

        // -------------------------------
        // STATIC ROUTE LOOKUP
        // -------------------------------
        if (isset($this->routes['static'][$method][$uri])) {

            $route = $this->routes['static'][$method][$uri];

            return $this->runRoute($route, [], $request);
        }

        // -------------------------------
        // DYNAMIC ROUTES
        // -------------------------------
        $group = $this->extractGroup($uri);

        $dynamicRoutes = $this->routes['dynamic'][$method][$group] ?? [];

        foreach ($dynamicRoutes as $route) {

            $matches = [];

            if (isset($route['pattern']) &&
                preg_match($route['pattern'], $uri, $matches)) {

                $params = array_filter(
                    $matches,
                    'is_string',
                    ARRAY_FILTER_USE_KEY
                );

                return $this->runRoute($route, $params, $request);
            }
        }

        $this->logError("Route not found: $uri");

        throw new RouteNotFoundException("Route not found: $uri");
    }

    // =========================================
    // LOG ERROR MESSAGES
    // =========================================
    private function logError(
        string $message
    ): void {
        
        $logFile   = dirname(__DIR__, 2) . '/storage/logs/router-error.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry     = "[{$timestamp}] {$message}\n";
        
        error_log($entry, 3, $logFile);
    }
}
