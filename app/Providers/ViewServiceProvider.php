<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\View;
use App\Contracts\SessionInterface;
use App\Contracts\CacheInterface;
// Import custom shared data classes if needed

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        container()
            ->singleton(
                View::class
            );
    }
    
    public function boot(): void
    {
        $view = container()
            ->get(View::class);

        $session = container()
            ->get(SessionInterface::class);

        $cache = container()
            ->get(CacheInterface::class);

        // CSRF Token
        $csrfToken = $session->token();

        // -----------------------------------------
        // SHARE VIEW DATA GLOBALLY ACROSS APP
        // ----------------------------------------

        // Global Shared Data
        $view->share('appName', config('app.name'));
        $view->share('appUrl', config('app.url'));
        $view->share('csrfToken', $csrfToken);
        // Additional shared data can be added here as needed
    }
}