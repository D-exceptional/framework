<?php

declare(strict_types=1);

// =========================================
// DEFINE BASE PATH
// =========================================
define('BASE_PATH', dirname(__DIR__, 2));

// =========================================
// AUTOLOAD & BOOT
// =========================================
$app = require_once BASE_PATH . '/bootstrap/app.php';

// =========================================
// IMPORT QUEUE WORKER CLASS
// =========================================
use App\Queue\QueueWorker;

// ===================================================
// INITIALIZE QUEUE WORKER
// ===================================================
$worker = $app->container()->get(QueueWorker::class);

// =============================================
// GET WORKER TYPE FROM CLI PARAM (emails, otp)
// =============================================
$queue = $argv[1] ?? 'default';

// =========================================
// RUN QUEUE WORKER
// =========================================
$worker->run($queue);

