<?php

declare(strict_types=1);

define(
    'ROOT_PATH', 
    dirname(__DIR__)
);

require_once ROOT_PATH . '/vendor/autoload.php';

use App\Core\Application;

// ------------------------------------
// BOOT APPLICATION
// ------------------------------------
$app = new Application();

$app->boot();

// make app globally accessible
$GLOBALS['app'] = $app;

// return app instance
return $app;