<?php

declare(strict_types=1);

/*
|--------------------------------------------
| Define base path
|--------------------------------------------
*/
define('BASE_PATH', 
    dirname(__DIR__, 2)
);

$cacheFile = BASE_PATH . '/storage/framework/cache/route/routes.php';

if (file_exists($cacheFile)) {

    unlink($cacheFile);
    echo "Route cache cleared\n";

} else {
    
    echo "No cache found\n";
}