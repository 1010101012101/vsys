<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ProductPriceServiceProvider extends ServiceProvider
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
            'App\Repositories\ProductPriceRepositoryInterface',
            'App\Repositories\Eloquent\ProductPriceEloquentRepository'
        );

        /*
         * =============== SERVICE ===============
         * */
        $this->app->bind(
            'App\Services\ProductPriceServiceInterface',
            'App\Services\Implement\ProductPriceService'
        );
    }
}
