<?php

declare(strict_types=1);

define(
    'BASE_PATH',
    dirname(__DIR__)
);

// Enable full error reporting (local development, change both to 0 on production)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

error_reporting(E_ALL);

// ------------------------------------
// IMPORT REQUIRED CLASSES
// ------------------------------------
use App\Core\Kernel;
use App\Http\Request;

// ------------------------------------
// BOOT APPLICATION 
// ------------------------------------
$app = require BASE_PATH . '/bootstrap/bootstrap.php';

// ------------------------------------
// LOAD REQUEST CLASS
// ------------------------------------
$request = $app
    ->container()
    ->get(Request::class);

// ------------------------------------
// LOAD KERNEL CLASS
// ------------------------------------
$kernel = $app
    ->container()
    ->get(Kernel::class);

// ------------------------------------
// HANDLE INCOMING REQUEST
// ------------------------------------
$kernel->handle($request);