<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// if (tenant()) {
Log::info(['tenant route' => tenant()]);
// }

Route::group(['prefix' => config('sanctum.prefix', 'sanctum')], static function () {
    Route::get('/csrf-cookie', [CsrfCookieController::class, 'show'])
        ->middleware([
            'universal',
        ])->name('sanctum.csrf-cookie');
});

Route::get('/', function () {
    return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
});
