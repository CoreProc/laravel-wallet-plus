<?php

namespace CoreProc\WalletPlus;

use Illuminate\Support\ServiceProvider;

class WalletPlusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('wallet-plus.php'),
            ], 'config');

            /*
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'wallet-plus');

            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/wallet-plus'),
            ], 'views');
            */
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'wallet-plus');
    }
}
