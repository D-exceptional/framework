<?php

declare(strict_types=1);

define(
    'BASE_PATH',
    dirname(__DIR__)
);

// Enable full error reporting (local development, change both to 0 on production)
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

error_reporting(E_ALL);

// ------------------------------------
// IMPORT REQUIRED CLASSES
// ------------------------------------
use App\Core\Kernel;
use App\Http\Request;
use App\Http\ResponseEmitter;

// ------------------------------------
// BOOT APPLICATION 
// ------------------------------------
$app = require BASE_PATH . '/bootstrap/app.php';

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
// LOAD HTTP RESPONSE EMITTER
// ------------------------------------
$emitter = $app
    ->container()
    ->get(ResponseEmitter::class);

// ------------------------------------
// HANDLE INCOMING REQUEST
// ------------------------------------
$response = $kernel->handle($request);

// ------------------------------------
// EMIT RESPONSE TO CLIENT
// ------------------------------------
$emitter->emit($response);
