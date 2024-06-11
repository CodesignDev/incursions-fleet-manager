<?php

namespace App\Http\Controllers;

use App\Http\Resources\CharacterResource;
use App\Http\Resources\WaitlistCategoryResource;
use App\Models\Category;
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

        // Get the list of categories and the fleets / waitlists available within each
        $categories = Category::query()
            ->whereHas('waitlists')
            ->with(['fleets', 'waitlists.entries'])
            ->get();

        return inertia('Waitlist/ViewWaitlist', [
            'characters' => CharacterResource::collection($characters),
            'categories' => WaitlistCategoryResource::collection($categories),
        ]);
    }
}
