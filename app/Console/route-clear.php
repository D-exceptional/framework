<?php

// =========================================
// DEFINE BASE PATH
// =========================================
define('BASE_PATH', dirname(__DIR__, 2));

// =========================================
// GET FILE CACHE DATA
// =========================================
$cacheFile = BASE_PATH . '/storage/cache/route/routes.php';

// =========================================
// DELETE CACHE FILE IF EXISTS
// =========================================
if (file_exists($cacheFile)) {
    unlink($cacheFile);
    echo "Route cache cleared\n";
} else {
    echo "No cache found\n";
}