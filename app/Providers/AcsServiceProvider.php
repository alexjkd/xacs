<?php

namespace App\Providers;

use App\Models\ACS;
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
        $this->app->bind('soap', \App\Models\SoapEngine::class);
        //bind it to a share object to use as a singleton object
        $this->app->singleton('acs',function(){
            return ACS::singleton();
        });
    }
}
