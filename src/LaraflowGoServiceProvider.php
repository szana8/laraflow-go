<?php

namespace szana8\LaraflowGo;

use Illuminate\Support\ServiceProvider;

class LaraflowGoServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../resources/assets/js/components' => base_path('resources/assets/js/components/laraflow-go'),
                __DIR__.'/../resources/assets/js/LaraflowGo.js' => base_path('resources/assets/js/LaraflowGo.js'),
                __DIR__.'/../resources/assets/js/event-bus.js' => base_path('resources/assets/js/event-bus.js'),
            ], 'laraflow-go-components');

        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }



}