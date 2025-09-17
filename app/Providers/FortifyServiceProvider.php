<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->bindCreateNewUser();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::loginView(fn () => Inertia::render('auth/Login'));
        Fortify::registerView(fn () => Inertia::render('auth/Register'));
        Fortify::requestPasswordResetLinkView(fn () => Inertia::render('auth/ForgotPassword'));
        Fortify::resetPasswordView(
            fn ($request) => Inertia::render('auth/ResetPassword', [
                'token' => $request->route('token'),
                'email' => $request->email,
            ])
        );

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge'));
        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }

    private function bindCreateNewUser(): void
    {
        $this->app->bind(\Laravel\Fortify\Contracts\CreatesNewUsers::class, function ($app) {
            if (function_exists('tenant') && tenant()) {
                return new \Modules\OrganizationAdmin\Actions\Fortify\CreateNewUser;
            }

            return new \App\Actions\Fortify\CentralUsers\CreateNewUser;
        });
    }
}
