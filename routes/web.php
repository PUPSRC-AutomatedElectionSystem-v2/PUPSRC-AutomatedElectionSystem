<?php

use App\Http\Controllers\TenantsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
// require __DIR__ . '/auth.php';

// Tenant management (central app)
Route::get('/tenants/create', [TenantsController::class, 'create'])->name('tenants.create');
Route::post('/tenants', [TenantsController::class, 'store'])->name('tenants.store');
