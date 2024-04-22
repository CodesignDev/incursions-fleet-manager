<?php

namespace App\Http\Controllers;

use App\Models\GiceGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Laravel\Socialite\Contracts\User as OidcUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GiceAuthController extends Controller
{
    /**
     * Start the GICE login flow
     */
    public function login(): Response
    {
        $redirect = Socialite::driver('gice')->redirect();

        return Inertia::location($redirect);
    }

    /**
     * Process the GICE OIDC callback request.
     */
    public function callback(Request $request): RedirectResponse
    {
        // Get the user from GICE
        $giceUser = Socialite::driver('gice')->user();

        // Attempt to find the user in the database
        $user = User::updateOrCreate([
            'id' => $giceUser->id,
        ], [
            'name' => $giceUser->name,
            'username' => $giceUser->username,
        ]);

        $this->assignGiceGroups($user, $giceUser);

        // Login the user and regenerate the session
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): Response
    {
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return Inertia::Location('https://goonfleet.com/');
    }

    /**
     * Assign user groups to the authenticating user based on their
     * groups that they are a member of on GICE.
     */
    protected function assignGiceGroups(User $user, OidcUser $oidcUser): void
    {
        // Get the list of groups from the user
        $primaryGroup = $oidcUser->primary_group;
        $userGroups = $oidcUser->groups ?? [];

        // Collate the list of groups
        $groups = collect([$primaryGroup, ...$userGroups])->unique()->sort();

        // Get the list of groups that is known by the application
        $knownGroups = GiceGroup::whereIn('id', $groups)->get();
        $missingGroups = $groups->diff($knownGroups->pluck('id'));

        // Create the groups that are missing in the database
        try {
            DB::transaction(function () use ($missingGroups) {
                collect($missingGroups)
                    ->map(fn($id) => ['id' => $id])
                    ->mapInto(GiceGroup::class)
                    ->each->save();
            });
        } catch (Throwable) {
            // Skip the creation of groups if there is an error
            $groups = $knownGroups->pluck('id');
        }

        // Attach the groups to the user
        $user->giceGroups()->sync($groups);

        // Flag the primary group
        $user->giceGroups()->updateExistingPivot($primaryGroup, ['is_primary_group' => true]);
    }
}
