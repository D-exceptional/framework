<?php

    declare(strict_types=1);

    // =========================================
    // GLOBAL CONFIGURATION & ERROR HANDLING
    // =========================================
    define('BASE_PATH', dirname(__DIR__));

    // Enable full error reporting (for local development)
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    // Log all errors to a file at project root
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/storage/logs/router-error.log');


    // =========================================
    // AUTOLOADING (Composer)
    // =========================================
    require_once BASE_PATH . '/bootstrap.php';


    // ===============================================
    // IMPORT CLASSES (for type hinting and clarity)
    // ================================================
    use App\Routing\Router;
    use App\Http\Request;
    use App\Http\Response;
    use App\Exceptions\MiddlewareException;
    use App\Exceptions\ValidationException;
    use App\Exceptions\RouteNotFoundException;


    // =========================================
    // INITIALIZE ROUTER FROM CONTAINER
    // =========================================
    $router = $app->container()->get(Router::class);


    // ======================================================================
    // LOAD ROUTES (from cache if available, otherwise from routes/api.php)
    // =======================================================================
    $routeCache = BASE_PATH . '/storage/cache/route/routes.php';

    if (file_exists($routeCache)) {

        $router->setRoutes(
            require $routeCache
        );

    } else {

        require_once BASE_PATH . '/routes/api.php';
    }


    // =========================================
    // BOOTSTRAP REQUEST OBJECT FROM CONTAINER
    // =========================================
    $request = $app->container()->get(Request::class);


    // ==========================================
    // INITIALIZE RESPONSE OBJECT FROM CONTAINER
    // ==========================================
    $response = $app->container()->get(Response::class);


    // =============================================================================
    // DISPATCH THE REQUEST THROUGH THE ROUTER AND HANDLE ANY EXCEPTIONS GLOBALLY
    // =============================================================================
    try {
        $router->dispatch($request);
    } 
    catch (MiddlewareException $e) {
        if ($e->action === 'redirect' && $e->redirect) {
            header('Location: ' . $e->redirect);
        } else {
            $response->error($e->getMessage(), $e->status, null);
        }
    } 
    catch (ValidationException $e) {
        $response->error($e->getMessage(), $e->status, $e->errors ? $e->errors : null);
    }
    catch (RouteNotFoundException $e) {
        $response->error($e->getMessage(), 404, null);
    } catch (\Throwable $e) {
        $response->error($e->getMessage(), 500, null);
    }

    exit;
