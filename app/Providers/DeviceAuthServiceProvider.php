<?php

namespace App\Providers;

use Auth;
use App\Services\Auth\DeviceGuard;
use Illuminate\Support\ServiceProvider;

class DeviceAuthServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::extend('device_token', function($app, $name, array $config) {

            return new DeviceGuard(Auth::createUserProvider($config['provider']), $app->request);
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}