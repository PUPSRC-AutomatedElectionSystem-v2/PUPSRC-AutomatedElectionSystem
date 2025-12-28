<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(\App\Http\Middleware\HandleInertiaRequests::class, function ($app) {
            return tenancy()->initialized
                ? new \Modules\Shared\Http\Middleware\HandleTenantInertiaRequests
                : new \App\Http\Middleware\HandleInertiaRequests;
        });
    }
}
