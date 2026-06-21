<?php

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/vendor/autoload.php';

use App\Core\Application;

// ------------------------------------
// BOOT APPLICATION
// ------------------------------------
$app = new Application();

$app->loadEnvironment();
$app->loadConfiguration();
$app->registerBindings();
$app->bootServices();

// make globally accessible
$GLOBALS['app'] = $app;

// return container to router
return $app->container();