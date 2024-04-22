<?php

use App\Http\Controllers\GiceAuthController;
use Illuminate\Support\Facades\Route;

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

Route::middleware('guest')->group(function() {
    Route::get('/login', [GiceAuthController::class, 'login'])
        ->name('login');

    Route::get('/login/callback', [GiceAuthController::class, 'callback']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [GiceAuthController::class, 'logout'])
                ->name('logout');
});
