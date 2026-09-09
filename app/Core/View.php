<?php

declare(strict_types=1);

namespace App\Core;

class View
{
     /**
     * Shared data available to all views.
     *
     * @var array<string, mixed>
     */
    protected array $shared = [];

    /**
     * Share data with all views.
     */
    public function share(
        string $key,
        mixed $value
    ): void {
        $this->shared[$key] = $value;
    }

    /**
     * Render a view and return its HTML.
     *
     * @param string $view
     * @param array<string, mixed> $data
     *
     * @throws \RuntimeException
     */
    public function render(
        string $view,
        array $data = []
    ): string {

        // Check if views are enabled.
        if (!config('app.display.use_views')) {
            throw new \RuntimeException(
                'Views are disabled in the configuration.'
            );
        }

        // Resolve view directory.
        $viewPath = config('app.base_path')
            . DIRECTORY_SEPARATOR
            . config('app.display.view_path');

        // Convert dot notation.
        $view = str_replace(
            '.',
            DIRECTORY_SEPARATOR,
            $view
        );

        $file = $viewPath
            . DIRECTORY_SEPARATOR
            . $view
            . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException(
                "View [{$view}] not found."
            );
        }

        // Merge shared data with view-specific data.
        $data = array_merge(
            $this->shared,
            $data
        );

        // Extract variables.
        extract($data, EXTR_SKIP);

        ob_start();

        require $file;

        return ob_get_clean() ?: '';
    }
}