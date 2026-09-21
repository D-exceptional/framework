<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Base Path
|--------------------------------------------------------------------------
*/

define('BASE_PATH', dirname(__DIR__, 2));

/*
|--------------------------------------------------------------------------
| Imports
|--------------------------------------------------------------------------
*/

use App\Routing\Router;

/*
|--------------------------------------------------------------------------
| Bootstrap Framework
|--------------------------------------------------------------------------
*/

$app = require_once BASE_PATH . '/bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Resolve Router
|--------------------------------------------------------------------------
*/

$router = $app->container()->get(Router::class);

/*
|--------------------------------------------------------------------------
| Load Route Collections
|--------------------------------------------------------------------------
*/

$routeCollections = config(
    'router.collections',
    ['api', 'web']
);

$totalCollections = 0;

foreach ($routeCollections as $collection) {

    $file = BASE_PATH . "/routes/{$collection}.php";

    if (!file_exists($file)) {

        echo "Skipping missing route file: {$collection}.php\n";

        continue;
    }

    $router->setCollection($collection);

    require $file;

    $totalCollections++;
}

/*
|--------------------------------------------------------------------------
| Reset Active Collection Tracker
|--------------------------------------------------------------------------
*/

$router->resetCollection();


/*
|--------------------------------------------------------------------------
| Convert Route Objects To Cacheable Arrays
|--------------------------------------------------------------------------
*/

$cacheRoutes = $router->toCacheArray();

/*
|--------------------------------------------------------------------------
| Cache Location
|--------------------------------------------------------------------------
*/

$cacheDirectory = BASE_PATH . '/storage/framework/cache/route';

$cacheFile = $cacheDirectory . '/routes.php';

/*
|--------------------------------------------------------------------------
| Create Cache Directory
|--------------------------------------------------------------------------
*/

if (!is_dir($cacheDirectory)) {

    mkdir(
        $cacheDirectory,
        0777,
        true
    );
}

/*
|--------------------------------------------------------------------------
| Write Route Cache
|--------------------------------------------------------------------------
*/

$cacheContents =
    "<?php\n\n" .
    "return " .
    var_export($cacheRoutes, true) .
    ";\n";

file_put_contents(
    $cacheFile,
    $cacheContents,
    LOCK_EX
);

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalRoutes = 0;

foreach ($cacheRoutes as $collection => $routeSet) {

    $static = 0;
    $dynamic = 0;

    /*
    |--------------------------------------------------------------------------
    | Static Count
    |--------------------------------------------------------------------------
    */

    foreach ($routeSet['static'] ?? [] as $methodRoutes) {

        $static += count($methodRoutes);
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic Count
    |--------------------------------------------------------------------------
    */

    foreach ($routeSet['dynamic'] ?? [] as $methodRoutes) {

        foreach ($methodRoutes as $group) {

            $dynamic += count($group);
        }
    }

    $count = $static + $dynamic;

    $totalRoutes += $count;

    echo sprintf(
        "%-10s : %d routes\n",
        strtoupper($collection),
        $count
    );
}

/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

echo "----------------------------------------\n";

echo "Collections : {$totalCollections}\n";

echo "Total Routes: {$totalRoutes}\n";

echo "----------------------------------------\n";

echo "Route cache generated successfully.\n";

echo "Cache File : {$cacheFile}\n";