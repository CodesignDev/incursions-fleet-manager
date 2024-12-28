<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\PollsBatches;
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
    use PollsBatches;

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

    public function checkProgress(string $scanJobId)
    {
        // Poll the batch and return the relevant status
        return $this->pollBatch(
            $scanJobId,
            whenSuccessful: fn ($batch) => response()->json(['message' => 'Successful']),
            whenFailed: fn () => response()->json(['error' => 'failed'], 422),
            whenInProgress: fn () => response()->noContent(202),
            whenNotFound: fn () => response()->json(['error' => 'Not Found'], 404)
        );
    }

    public function cancel(string $scanJobId)
    {
        // Find the batch with the passed id
        $batch = Bus::findBatch($scanJobId);

        // If there is no batch with this ID, return a 404
        if (is_null($batch)) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        // If the batch has already finished, then return a 422
        if ($batch->finished()) {
            return response()->json(['error' => 'Already finished'], 422);
        }

        // Cancel the batch and return a 204
        $batch->cancel();
        return response()->noContent();
    }
}
