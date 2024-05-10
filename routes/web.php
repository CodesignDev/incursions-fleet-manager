<?php

use App\Http\Controllers\WaitlistController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
]);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::prefix('/waitlist/{waitlist}')
        ->as('waitlist.')
        ->controller(WaitlistController::class)
        ->group(function () {
            Route::post('/', 'joinWaitlist')->name('join');
            Route::delete('/', 'leaveWaitlist')->name('leave');
        });
});

require __DIR__.'/auth.php';
