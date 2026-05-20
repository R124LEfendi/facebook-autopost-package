<?php

namespace R124LEfendi\FacebookAutopost;

use Illuminate\Support\ServiceProvider;
use R124LEfendi\FacebookAutopost\Console\Commands\FacebookPostCommand;

class FacebookAutopostServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Facebook Service
        $this->app->singleton(\R124LEfendi\FacebookAutopost\Services\FacebookService::class, function ($app) {
            return new \R124LEfendi\FacebookAutopost\Services\FacebookService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load migrations from package database directory
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views from package resources directory
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'facebook-autopost');

        // Load web routes from package routes directory
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Register Console Command and Assets for CLI
        if ($this->app->runningInConsole()) {
            // Publish Views for independent customization
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/facebook-autopost'),
            ], 'facebook-autopost-views');

            $this->commands([
                FacebookPostCommand::class,
            ]);
        }
    }
}
