<?php

namespace App\Http\Controllers;

use App\Http\Resources\CharacterResource;
use App\Http\Resources\FleetResource;
use App\Models\Fleet;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function list(): Responsable
    {
        // Get the list of fleets
        $fleets = Fleet::query()
            ->whereTracked()
            ->with(['members', 'boss.user'])
            ->withCount('members')
            ->get();

        return inertia('Fleets/FleetList', [
            'fleets' => FleetResource::collection($fleets),
        ]);
    }

    public function register(Request $request): Responsable
    {
        // Get the current user
        $user = $request->user();

        // Pull various data from the user
        $characters = $user->characters()->whereWhitelisted()->get();

        return inertia('Fleets/RegisterFleet', [
            'characters' => CharacterResource::collection($characters),
        ]);
    }
}
