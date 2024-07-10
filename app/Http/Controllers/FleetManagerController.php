<?php

namespace App\Http\Controllers;

use App\Enums\FleetManagementPage;
use App\Http\Resources\FleetResource;
use App\Http\Resources\FleetWaitlistResource;
use App\Models\Fleet;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FleetManagerController extends Controller
{
    public function __invoke(Request $request, Fleet $fleet, ?FleetManagementPage $page = FleetManagementPage::WAITLIST): Responsable
    {
        return inertia('Fleets/FleetManager', [
            'default_tab' => $page,
            'fleet' => fn () => new FleetResource($fleet),
            'waitlist_entries' => Inertia::lazyUnless(
                $page === FleetManagementPage::WAITLIST,
                function () use ($fleet) {
                    $fleet->load([
                        'waitlists.entries',
                        'waitlists.entries.user',
                    ]);

                    return FleetWaitlistResource::collection($fleet->waitlists);
                }
            ),
            'fleet_members' => Inertia::lazyUnless(
                $page === FleetManagementPage::FLEET_MEMBERS,
                fn () => []
            ),
            'fleet_settings' => Inertia::lazyUnless(
                $page === FleetManagementPage::FLEET_SETTINGS,
                fn () => []
            )
        ]);
    }
}
