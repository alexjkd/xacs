<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CpeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Interfaces\ICpe', 'App\Models\CPE');
    }
}
