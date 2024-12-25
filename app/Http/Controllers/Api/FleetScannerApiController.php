<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ScanForUserOwnedFleets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class FleetScannerApiController extends Controller
{
    public function startScan(Request $request)
    {
        // Get the current user
        $user = $request->user();

        // Queue up the fleet scan job using the current user
        $batch = Bus::batch([new ScanForUserOwnedFleets($user)])->dispatch();

        return response()->json(
            ['id' => $batch->id],
            201,
        );
    }
}
