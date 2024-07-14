<?php

namespace App\Http\Controllers;

use App\Http\Resources\CharacterResource;
use App\Http\Resources\DoctrineResource;
use App\Http\Resources\WaitlistCategoryResource;
use App\Models\Category;
use App\Models\Doctrine;
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
            ->with([
                'fleets' => fn($query) => $query
                    ->with(['boss', 'members'])
                    ->withCount('members'),
                'waitlists' => fn ($query) => $query
                    ->with(['entries'])
                    ->withCount('entries')
            ])
            ->get();

        // Get the doctrines for the waitlists that are available
        $doctrines = Doctrine::whereIn('id', $categories->flatMap(fn ($category) => $category->waitlists)->pluck('doctrine_id'))->get();

        return inertia('Waitlist/ViewWaitlist', [
            'characters' => CharacterResource::collection($characters),
            'categories' => WaitlistCategoryResource::collection($categories),
            'doctrines' => DoctrineResource::collection($doctrines),
        ]);
    }
}
