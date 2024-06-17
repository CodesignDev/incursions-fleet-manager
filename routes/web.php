<?php

use App\Http\Controllers\FleetController;
use App\Http\Controllers\RegisterFleetController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\WaitlistDashboardController;
use App\Http\Controllers\WaitlistUpdateCharactersController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Dashboard')->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::prefix('/waitlist')->as('waitlist.')->group(function () {
        Route::get('/', [WaitlistDashboardController::class, '__invoke'])->name('dashboard');

        Route::prefix('/{waitlist}')->controller(WaitlistController::class)->group(function () {
            Route::post('/', 'joinWaitlist')->name('join');
            Route::delete('/', 'leaveWaitlist')->name('leave');

            Route::put('/', [WaitlistUpdateCharactersController::class, '__invoke'])->name('update');
        });
    });

    Route::prefix('/fleets')->as('fleets.')->group(function () {
        Route::get('/', [FleetController::class, 'list'])->name('list');

        Route::get('/register', [FleetController::class, 'register'])->name('register');
        Route::post('/register', [RegisterFleetController::class, '__invoke']);

        Route::prefix('/{fleet}')->group(function () {
            Route::get('/', [FleetController::class, 'show'])->name('show');
        });
    });

});

require __DIR__.'/auth.php';
