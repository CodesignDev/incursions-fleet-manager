<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ClearPreviousFleetScans;
use App\Jobs\ScanForUserOwnedFleets;
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
        ])->dispatch();

        return response()->json(
            ['id' => $batch->id],
            201,
        );
    }
}
