<?php

namespace App\Providers;

use App\Services\SMSService;
use Illuminate\Support\ServiceProvider;

class SMSServiceProvider extends ServiceProvider
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
        $this->app->singleton('SMS', function() {
            return new SMSService();
        });

        $this->app->bind('App\Contracts\SMSServiceContract', function() {
            return new SMSService();
        });
    }
}
