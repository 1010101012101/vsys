<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PositionServiceProvider extends ServiceProvider
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
            'App\Repositories\PositionRepositoryInterface',
            'App\Repositories\Eloquent\PositionEloquentRepository'
        );

        /*
         * =============== SERVICE ===============
         * */
        $this->app->bind(
            'App\Services\PositionServiceInterface',
            'App\Services\Implement\PositionService'
        );
    }
}
