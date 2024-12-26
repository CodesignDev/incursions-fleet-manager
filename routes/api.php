<?php

use App\Http\Controllers\Api\FleetScannerApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Fleets
    Route::prefix('fleet')->group(function () {

        // Fleet scanner
        Route::prefix('/scan')->controller(FleetScannerApiController::class)->group(function () {
            Route::post('/', 'startScan')->name('fleet-scanner-api.start-scan');
            Route::get('/{jobId}', 'checkProgress')->name('fleet-scanner-api.check-progress');
            Route::delete('/{jobId}', 'cancel')->name('fleet-scanner-api.cancel');
        });
    });

});
