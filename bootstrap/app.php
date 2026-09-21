<?php

declare(strict_types=1);

define(
    'ROOT_PATH',
    dirname(__DIR__)
);

// ------------------------------------
// LOAD VENDOR AUTOLOADER
// ------------------------------------

require_once ROOT_PATH . '/vendor/autoload.php';

// ------------------------------------
// LOAD BOOTSTRAP ENV HELPER
// ------------------------------------

require_once ROOT_PATH . '/bootstrap/env.php';

// ------------------------------------
// IMPORT APPLICATION CLASS
// ------------------------------------

use App\Core\Application;

// ------------------------------------
// BOOT APPLICATION
// ------------------------------------

$app = new Application();

$app->boot();

// ------------------------------------
// LOAD APPLICATION HELPERS
// ------------------------------------

require_once ROOT_PATH . '/bootstrap/helpers.php';

// return app instance
return $app;