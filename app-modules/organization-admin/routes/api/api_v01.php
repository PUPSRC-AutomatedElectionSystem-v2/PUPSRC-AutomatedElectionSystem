<?php

use Illuminate\Support\Facades\Route;
use Modules\OrganizationAdmin\Http\Controllers\Api\V01\CandidateController;
use Modules\OrganizationAdmin\Http\Controllers\Api\V01\PositionController;
use Modules\OrganizationAdmin\Http\Controllers\Api\V01\RegistrationScheduleController;
use Modules\OrganizationAdmin\Http\Controllers\Api\V01\VoterController;
use Modules\OrganizationAdmin\Http\Controllers\Api\V01\VotingScheduleController;

Route::prefix('v1')->name('v1.')->group(function () {
    // Read positions (used by the candidate form)
    Route::get('/position', [PositionController::class, 'index'])->name('position.index');
    Route::post('/position', [PositionController::class, 'store'])->name('position.store');

    Route::prefix('registration')->name('registration.')->group(function () {
        Route::post('/schedule', [RegistrationScheduleController::class, 'store'])->name('schedule.store');
    });

    Route::prefix('voting')->name('voting.')->group(function () {
        Route::post('/schedule', [VotingScheduleController::class, 'store'])->name('schedule.store');
    });

    Route::post('/candidates', [CandidateController::class, 'store'])->name('candidates.store');

    Route::post('/voters/import', [VoterController::class, 'import'])->name('voters.import');
});
