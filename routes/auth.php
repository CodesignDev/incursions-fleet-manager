<?php

use App\Http\Controllers\GiceAuthController;
use App\Http\Controllers\GiceOidcCallbackController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function() {
    Route::get('/login', [GiceAuthController::class, 'login'])->name('login');
    Route::get('/login/callback', [GiceOidcCallbackController::class, 'callback']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [GiceAuthController::class, 'logout'])->name('logout');
});
