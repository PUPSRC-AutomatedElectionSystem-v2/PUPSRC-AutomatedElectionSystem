<?php

use App\Http\Controllers\Api\V01\TenantsController;
use App\Http\Controllers\Api\V01\UserDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function () {
    Route::post('/tenants', [TenantsController::class, 'store'])->name('tenants.store');

    Route::prefix('users/data')->name('users.data.')->group(function () {
        Route::post('/', [UserDataController::class, 'store'])->name('store');
    });
});
