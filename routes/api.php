<?php

use App\Http\Controllers\Api\FleetScannerApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Fleets
    Route::prefix('fleet')->group(function () {

        // Fleet scanner
        Route::post('/scan', [FleetScannerApiController::class, 'startScan'])
            ->name('fleet-scanner-api.start-scan');
        Route::get('/scan/{jobId}', [FleetScannerApiController::class, 'checkProgress'])
            ->name('fleet-scanner-api.check-progress');
    });

});
