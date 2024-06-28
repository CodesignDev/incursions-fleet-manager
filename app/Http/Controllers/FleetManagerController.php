<?php

namespace App\Http\Controllers;

use App\Enums\FleetManagementPage;
use App\Http\Resources\FleetResource;
use App\Models\Fleet;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FleetManagerController extends Controller
{
    public function __invoke(Request $request, Fleet $fleet, ?FleetManagementPage $page = FleetManagementPage::WAITLIST): Responsable
    {
        return inertia('Fleets/FleetManager', [
            'default_page_tab' => $page,
            'fleet' => fn () => new FleetResource($fleet),
            'waitlist_entries' => Inertia::lazy(fn () => []),
        ]);
    }
}
