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
        if (! class_exists('CreateWalletPlusTables')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_wallet_plus_tables.php.stub' => database_path('migrations/' .
                    date('Y_m_d_His', time()) . '_create_wallet_plus_tables.php'),
            ], 'migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'wallet-plus');
    }
}
