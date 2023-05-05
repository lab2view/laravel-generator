<?php

namespace Lab2view\Generator;

use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/lab2view-generator.php',
            'core-generator'
        );
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Lab2view\Generator\Console\Commands\Generate::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/lab2view-generator.php' => config_path('lab2view-generator.php'),
        ]);
    }
}
