<?php

use App\Http\Controllers\Api\FleetScannerApiController;
use App\Http\Controllers\Api\FleetLinkVerificationApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Fleets
    Route::prefix('fleet')->group(function () {

        // Fleet scanner
        Route::prefix('/scan')->controller(FleetScannerApiController::class)->group(function () {
            Route::post('/', 'startScan')->name('fleet-scanner-api.start-scan');
            Route::get('/{scanJobId}', 'checkProgress')->name('fleet-scanner-api.check-progress');
            Route::delete('/{scanJobId}', 'cancel')->name('fleet-scanner-api.cancel');
        });

        // Verify Fleet Link
        Route::prefix('/verify-link')->controller(FleetLinkVerificationApiController::class)->group(function () {
            Route::post('/', 'startVerification')->name('fleet-verify-link-api.start-verification');
            Route::get('/{verifyJobId}', 'checkProgress')->name('fleet-verify-link-api.check-progress');
            Route::delete('/{verifyJobId}', 'cancel')->name('fleet-verify-link-api.cancel');

        });
    });

});
