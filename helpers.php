<?php

use App\Core\Config;
use App\Core\Application;
use App\Core\Container;

if (!function_exists('app')) {

    function app(): Application
    {
        return $GLOBALS['app'];
    }
}

if (!function_exists('container')) {

    function container(): Container
    {
        return app()->container();
    }
}

if (!function_exists('config')) {

    function config(
        string $key,
        mixed $default = null
    ): mixed {

        return app()
            ->container()
            ->get(Config::class)
            ->get($key, $default);
    }
}

if (!function_exists('env')) {

    function env(
        string $key,
        mixed $default = null
    ): mixed {

        return $_ENV[$key] ?? $default;
    }
}

/**
 * -----------------------------------------
 * Base project path
 * -----------------------------------------
 */
if (!function_exists('base_path')) {

    function base_path(
        string $path = ''
    ): string {

        return ROOT_PATH .
            ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

/**
 * -----------------------------------------
 * Storage path helper
 * -----------------------------------------
 */
if (!function_exists('storage_path')) {

    function storage_path(
        string $path = ''
    ): string {

        return base_path('storage') .
            ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

/*
config_path()
public_path()
resource_path()
database_path()
*/