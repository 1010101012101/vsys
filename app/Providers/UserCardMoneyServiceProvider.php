<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class UserCardMoneyServiceProvider extends ServiceProvider
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
            'App\Repositories\UserCardMoneyRepositoryInterface',
            'App\Repositories\Eloquent\UserCardMoneyEloquentRepository'
        );

        /*
         * =============== SERVICE ===============
         * */
        $this->app->bind(
            'App\Services\UserCardMoneyServiceInterface',
            'App\Services\Implement\UserCardMoneyService'
        );
    }
}
