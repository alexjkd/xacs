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
        $this->app->bind('App\Interface\IDataModelContract','App\Models\DataModel');
        /*
        $this->app->singleton('App\Models\ACS', function ($app) {
            return new \App\Models\ACS($app->make('App\Models\Soap'));
        });
        */
        $this->app->bind('soap', \App\Models\SoapEngine::class);
    }
}
