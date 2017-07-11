<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class LocationServiceProvider extends ServiceProvider
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
            'App\Repositories\NationRepositoryInterface',
            'App\Repositories\Eloquent\NationEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\CityRepositoryInterface',
            'App\Repositories\Eloquent\CityEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\DistrictRepositoryInterface',
            'App\Repositories\Eloquent\DistrictEloquentRepository'
        );
        $this->app->bind(
            'App\Repositories\WardRepositoryInterface',
            'App\Repositories\Eloquent\WardEloquentRepository'
        );

        /*
         * =============== SERVICE ===============
         * */
        $this->app->bind(
            'App\Services\NationServiceInterface',
            'App\Services\Implement\NationService'
        );
        $this->app->bind(
            'App\Services\CityServiceInterface',
            'App\Services\Implement\CityService'
        );
        $this->app->bind(
            'App\Services\DistrictServiceInterface',
            'App\Services\Implement\DistrictService'
        );
        $this->app->bind(
            'App\Services\WardServiceInterface',
            'App\Services\Implement\WardService'
        );

        $this->app->bind(
            'App\Services\LocationServiceInterface',
            'App\Services\Implement\LocationService'
        );
    }
}
