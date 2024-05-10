<?php

namespace App\Http\Controllers;

use App\Http\Resources\CharacterResource;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;

class WaitlistDashboardController extends Controller
{
    public function __invoke(Request $request): Responsable
    {
        // Get the current user
        $user = $request->user();

        // Pull various data from the user
        $characters = $user->characters()->whereWhitelisted()->with('corporation.alliance')->get();

        return inertia('Waitlist/ViewWaitlist', [
            'characters' => CharacterResource::collection($characters),
        ]);
    }
}
