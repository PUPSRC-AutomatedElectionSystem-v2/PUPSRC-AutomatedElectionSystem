<?php

use App\Http\Controllers\Api\V01\TenantsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function () {
    Route::post('/tenants', [TenantsController::class, 'store'])->name('tenants.store');
});
