<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Application;
use App\Core\Container;
use App\Routing\UrlGenerator;

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

/**
 * -----------------------------------------
 * Route url helper
 * -----------------------------------------
 */
if (!function_exists('route')) {

    /**
     * Generate a URL from a named route.
     */
    function route(
        string $name,
        array $parameters = []
    ): string {

        return app()
            ->container()
            ->get(UrlGenerator::class)
            ->route(
                $name,
                $parameters
            );
    }
}

/**
 * -----------------------------------------------
 * Generate asset url with optional versioning
 * -----------------------------------------------
 */
if (!function_exists('asset')) {

    function asset(
        string $path
    ): string {

        $path = ltrim($path, '/');

        $url = '/assets/' . $path;

        return $url;
    }
}

/**
 * -----------------------------------------------
 * Generate asset url with full versioning
 * -----------------------------------------------
 */
if (!function_exists('asset_versioned')) {

    function asset_versioned(
        string $path
    ): string {

        $path = ltrim($path, '/');

        $url = '/assets/' . $path;

        $filePath = config('app.base_path')
            . DIRECTORY_SEPARATOR
            . 'public'
            . DIRECTORY_SEPARATOR
            . 'assets'
            . DIRECTORY_SEPARATOR
            . str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                $path
            );

        if (is_file($filePath)) {
            return $url . '?v=' . filemtime($filePath);
        }

        return $url;
    }
}
