<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ButtonProductServiceProvider extends ServiceProvider
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
        /*
         * =============== REPOSITORY ===============
         * */
        $this->app->bind(
            'App\Repositories\ButtonProductRepositoryInterface',
            'App\Repositories\Eloquent\ButtonProductEloquentRepository'
        );

        /*
         * =============== SERVICE ===============
         * */
        $this->app->bind(
            'App\Services\ButtonProductServiceInterface',
            'App\Services\Implement\ButtonProductService'
        );
    }
}
