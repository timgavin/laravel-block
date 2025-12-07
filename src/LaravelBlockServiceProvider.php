<?php

namespace TimGavin\LaravelBlock;

use Illuminate\Support\ServiceProvider;

class LaravelBlockServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'laravel-block-migrations');

        $this->publishes([
            __DIR__.'/../config/laravel-block.php' => config_path('laravel-block.php'),
        ], 'laravel-block-config');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-block.php', 'laravel-block');

        $this->app->singleton('laravel-block', function ($app) {
            return new LaravelBlockManager;
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['laravel-block'];
    }

    /**
     * Console-specific booting.
     */
    protected function bootForConsole(): void
    {
        //
    }
}
