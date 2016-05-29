<?php

namespace App\Providers;

use App\Services\IMService;
use Illuminate\Support\ServiceProvider;

class IMServiceProvider extends ServiceProvider
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
        //使用singleton绑定单例
        $this->app->singleton('IM', function() {
            return new IMService();
        });

        //使用bind绑定实例到接口以便依赖注入
        $this->app->bind('App\Services\IMService', function() {
            return new IMService();
        });
    }
}
