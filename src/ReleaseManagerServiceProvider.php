<?php

namespace Alegiac\ReleaseManager;

use Alegiac\ReleaseManager\Commands\ReleaseCommand;
use Alegiac\ReleaseManager\Commands\ReleaseSetupCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Release Manager Service Provider
 * 
 * Registers the release management commands and publishes scripts.
 * 
 * @package Alegiac\ReleaseManager
 */
class ReleaseManagerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/release-manager.php', 'release-manager'
        );

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReleaseCommand::class,
                ReleaseSetupCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__.'/../config/release-manager.php' => config_path('release-manager.php'),
            ], 'release-manager-config');

            // Publish scripts
            $this->publishes([
                __DIR__.'/../scripts/release-conventional.sh' => base_path('release-conventional.sh'),
                __DIR__.'/../scripts/release-setup.sh' => base_path('release-setup.sh'),
            ], 'release-manager-scripts');

            // Publish documentation
            $this->publishes([
                __DIR__.'/../stubs/RELEASING.md' => base_path('RELEASING.md'),
                __DIR__.'/../stubs/CHANGELOG.md' => base_path('CHANGELOG.md'),
            ], 'release-manager-docs');
        }
    }
}

