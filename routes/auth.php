<?php

use App\Http\Controllers\GiceAuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| This file contains all the authentication related routes for the
| application. These routes are loaded by the web.php route file so gets
| loaded within the "web" middleware group.
|
*/

Route::prefix('auth')->group(function() {

    // Login and callback routes
    Route::middleware('guest')->group(function() {
        Route::get('/login', [GiceAuthController::class, 'login'])
            ->name('auth.login');
        Route::get('/callback', [GiceAuthController::class, 'callback'])
            ->name('auth.callback');
    });
});
