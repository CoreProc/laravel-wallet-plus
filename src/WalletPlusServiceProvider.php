<?php

namespace CoreProc\WalletPlus;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class WalletPlusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Filesystem $filesystem): void
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/create_wallet_plus_tables.php.stub' =>
                $this->getMigrationFileName($filesystem),
        ], 'migrations');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'wallet-plus');
    }

    protected function getMigrationFileName(Filesystem $filesystem)
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path . '*_create_wallet_plus_tables.php');
            })->push($this->app->databasePath() . "/migrations/{$timestamp}_create_wallet_plus_tables.php")
            ->first();
    }
}
