<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ClearPreviousFleetScans;
use App\Jobs\CreateFleetsFromFleetScans;
use App\Jobs\ScanForUserOwnedFleets;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpFoundation\Response;

class FleetScannerApiController extends Controller
{
    public function startScan(Request $request): Response
    {
        // Get the current user
        $user = $request->user();

        // Queue up the fleet scan job using the current user
        $batch = Bus::batch([
            [
                new ClearPreviousFleetScans($user),
                new ScanForUserOwnedFleets($user),
            ]
        ])
            ->then(function (Batch $batch) use ($user) {
                if (!$batch->cancelled()) {
                    CreateFleetsFromFleetScans::dispatch($user);
                }
            })
            ->dispatch();

        return response()->json(
            ['id' => $batch->id],
            201,
        );
    }

    public function checkProgress(string $jobId)
    {
        // Find the batch with the passed id
        $batch = Bus::findBatch($jobId);

        // If no batch was found, return a 404 error
        if (is_null($batch)) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        // If the batch is still in progress, return a 202
        if (! $batch->finished() && ! $batch->hasFailures()) {
            return response()->noContent(202);
        }

        // If the batch failed, return a 422
        if ($batch->hasFailures()) {
            return response()->json(['error' => 'failed'], 422);
        }

        // If the batch succeeded then just return a 200
        return response()->json(['message' => 'Successful']);
    }

    public function cancel(string $jobId)
    {
        // Find the batch with the passed id
        $batch = Bus::findBatch($jobId);

        // If there is no batch with this ID, return a 404
        if (is_null($batch)) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        // Cancel the batch and return a 204
        $batch->cancel();
        return response()->noContent();
    }
}
