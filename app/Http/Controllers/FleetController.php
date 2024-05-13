<?php

namespace App\Http\Controllers;

use App\Http\Resources\FleetResource;
use App\Models\Fleet;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function list(Request $request): Responsable
    {
        // Get the list of fleets
        $fleets = Fleet::query()
            ->with('boss.user')
            ->withCount('members')
            ->get();

        return inertia('Fleets/FleetList', [
            'fleets' => FleetResource::collection($fleets),
        ]);
    }

    public function register(): Responsable
    {
        return inertia('Fleets/RegisterFleet', [
            'characters' => [],
        ]);
    }
}
