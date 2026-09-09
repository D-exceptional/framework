<?php

declare(strict_types=1);

namespace App\Core;

class Config
{
    protected array $items = [];

    // =========================================
    // LOAD ALL CONFIG FILES
    // =========================================
    public function load(
        string $path
    ): void {

        $files = glob($path . '/*.php');

        foreach ($files as $file) {

            $key = basename($file, '.php');

            $this->items[$key] = require $file;
        }
    }

    // =========================================
    // GET CONFIG VALUE
    // =========================================
    public function get(
        string $key,
        mixed $default = null
    ): mixed {

        $segments = explode('.', $key);

        $config = $this->items;

        foreach ($segments as $segment) {

            if (!isset($config[$segment])) {
                return $default;
            }

            $config = $config[$segment];
        }

        return $config;
    }
}