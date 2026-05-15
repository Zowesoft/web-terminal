<?php

namespace Zowesoft\WebTerminal;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class WebTerminalServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        // Register the terminal access middleware
        $router->aliasMiddleware(
            'terminal.access',
            \Zowesoft\WebTerminal\Http\Middleware\TerminalAccess::class
        );

        // Load package routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Load package views under the "web-terminal" namespace
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'web-terminal'
        );

        // Auto-load package migrations
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );

        // Publishable: config
        $this->publishes([
            __DIR__ . '/../config/web-terminal.php' => config_path('web-terminal.php'),
        ], 'web-terminal-config');

        // Publishable: views (so devs can customise the UI)
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/web-terminal'),
        ], 'web-terminal-views');

        // Publishable: migrations (so devs can modify before running)
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'web-terminal-migrations');
    }

    public function register(): void
    {
        // Merge defaults so the package works even without publishing config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/web-terminal.php',
            'web-terminal'
        );
    }
}
