<?php

namespace App\Providers;

use Illuminate\Foundation\Console\KeyGenerateCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(KeyGenerateCommand::class, \App\Console\Commands\KeyGenerateCommand::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
