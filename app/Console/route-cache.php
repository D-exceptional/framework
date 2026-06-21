<?php

// =========================================
// DEFINE BASE PATH
// =========================================
define('BASE_PATH', dirname(__DIR__, 2));

// =========================================
// IMPORT ROUTER CLASS
// =========================================
use App\Routing\Router;

// =========================================
// AUTLOAD & BOOT
// =========================================
require_once BASE_PATH . '/bootstrap.php';

// =========================================
// INITIALIZE ROUTER
// =========================================
$router = $app->container()->get(Router::class);

// =========================================
// LOAD API ROUTES
// =========================================
require_once BASE_PATH . '/routes/api.php';

// =========================================
// EXPORT COMPILED ROUTES TO CACHE FILE
// =========================================
$routes = $router->getRoutes();

$cacheFile = BASE_PATH . '/storage/cache/route/routes.php';

if (!is_dir(dirname($cacheFile))) {
    mkdir(dirname($cacheFile), 0777, true);
}

file_put_contents(
    $cacheFile,
    "<?php return " . var_export($routes, true) . ";"
);

echo "Routes cached successfully\n";