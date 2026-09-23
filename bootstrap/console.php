<?php

declare(strict_types=1);

define(
    'ROOT_PATH',
    dirname(__DIR__)
);

// LOAD AUTOLOAD FILE
require_once ROOT_PATH . '/vendor/autoload.php';

// LOAD BOOTSTRAP ENV HELPER
require_once ROOT_PATH . '/bootstrap/env.php';

// IMPORT APPLICATION CLASS
use App\Core\Application;

// CREATE APPLICATION
$app = new Application();

// LOAD APPLICATION HELPERS
require_once ROOT_PATH . '/bootstrap/helpers.php';

// BOOT APPLICATION IN CLI MODE
$app->boot(false);

return $app;