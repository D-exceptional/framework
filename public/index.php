<?php

declare(strict_types=1);

define(
    'BASE_PATH',
    dirname(__DIR__)
);


// Enable full error reporting (local development)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

error_reporting(E_ALL);


require BASE_PATH . '/bootstrap/bootstrap.php';


use App\Core\Kernel;
use App\Http\Request;

$request = $app
    ->container()
    ->get(Request::class);


$kernel = $app
    ->container()
    ->get(Kernel::class);


$kernel->handle($request);