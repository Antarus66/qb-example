<?php

namespace App\Providers;

use App\Services\Quickbooks\Contract\QBTokenRefresherInterface;
use App\Services\Quickbooks\QBTokenRefresher;
use Illuminate\Support\ServiceProvider;

class QuickbooksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(QBTokenRefresherInterface::class, QBTokenRefresher::class);
    }
}
