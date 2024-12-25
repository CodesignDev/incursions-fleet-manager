<?php

use App\Http\Controllers\FleetController;
use App\Http\Controllers\FleetManagerController;
use App\Http\Controllers\RegisterFleetController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\WaitlistDashboardController;
use App\Http\Controllers\WaitlistUpdateCharactersController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Dashboard')->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Waitlist
    Route::prefix('/waitlist')->as('waitlist.')->group(function () {
        Route::get('/', [WaitlistDashboardController::class, '__invoke'])->name('dashboard');

        Route::prefix('/{waitlist}')->controller(WaitlistController::class)->group(function () {
            Route::post('/', 'join')->name('join');
            Route::delete('/', 'leave')->name('leave');

            Route::put('/', [WaitlistUpdateCharactersController::class, '__invoke'])->name('update');
        });
    });

    // Fleet
    Route::prefix('/fleets')->as('fleets.')->group(function () {

        // Dashboard
        Route::get('/', [FleetController::class, 'list'])->name('list');

        // Register Fleet
        Route::get('/register', [FleetController::class, 'register'])->name('register');
        Route::post('/register', [RegisterFleetController::class, '__invoke']);

        // Fleet Manager
        Route::prefix('/{fleet}')->group(function () {
            Route::get('/{page?}', [FleetManagerController::class, '__invoke'])
                ->whereIn('page', \App\Enums\FleetManagementPage::cases())
                ->name('show');
        });
    });

});

require __DIR__.'/auth.php';
