<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AcsServiceProvider extends ServiceProvider
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
        $this->app->bind('App\Interfaces\ICpeContract', 'App\Models\CPE');
        $this->app->bind('App\Interfaces\IInformContract', 'App\Models\Inform');
    }
}
