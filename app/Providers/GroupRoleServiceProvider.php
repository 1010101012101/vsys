<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class GroupRoleServiceProvider extends ServiceProvider
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
            'App\Repositories\GroupRoleRepositoryInterface',
            'App\Repositories\Eloquent\GroupRoleEloquentRepository'
        );

        /*
         * =============== SERVICE ===============
         * */
        $this->app->bind(
            'App\Services\GroupRoleServiceInterface',
            'App\Services\Implement\GroupRoleService'
        );
    }
}
