<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HistoryInputOutputServiceProvider extends ServiceProvider
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
            'App\Repositories\HistoryInputOutputRepositoryInterface',
            'App\Repositories\Eloquent\HistoryInputOutputEloquentRepository'
        );

        /*
         * =============== SERVICE ===============
         * */
        $this->app->bind(
            'App\Services\HistoryInputOutputServiceInterface',
            'App\Services\Implement\HistoryInputOutputService'
        );
    }
}
