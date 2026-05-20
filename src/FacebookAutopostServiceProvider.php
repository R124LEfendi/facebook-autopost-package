<?php

namespace Tokalink\FacebookAutopost;

use Illuminate\Support\ServiceProvider;
use Tokalink\FacebookAutopost\Console\Commands\FacebookPostCommand;

class FacebookAutopostServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Facebook Service
        $this->app->singleton(\Tokalink\FacebookAutopost\Services\FacebookService::class, function ($app) {
            return new \Tokalink\FacebookAutopost\Services\FacebookService();
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

        // Register Console Command
        if ($this->app->runningInConsole()) {
            $this->commands([
                FacebookPostCommand::class,
            ]);
        }
    }
}
