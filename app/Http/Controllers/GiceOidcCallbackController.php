<?php

namespace App\Http\Controllers;

use App\Jobs\FetchUserCharactersFromGice;
use App\Models\GiceGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as OidcUser;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GiceOidcCallbackController extends Controller
{
    /**
     * List of groups that were removed from the user during the authentication flow.
     */
    protected array $removedGroups;

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
        $this->assignRoles($user, $giceUser);
        $this->fetchCharacters($user);

        // Login the user and regenerate the session
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
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
        $groupUpdates = $user->giceGroups()->sync($groups);

        // Flag the primary group
        $user->giceGroups()->updateExistingPivot($primaryGroup, ['is_primary_group' => true]);

        // Save the list of updated groups
        $this->removedGroups = Arr::get($groupUpdates, 'detached', []);
    }

    /**
     * Assign the relevant roles to the user based on their group membership.
     */
    protected function assignRoles(User $user, OidcUser $oidcUser): void
    {
        // Extract the list of groups from the OIDC user
        $groupIds = $oidcUser->groups ?? [];

        // If there are no groups, exit
        if (blank($groupIds)) {
            return;
        }

        // Get the groups from the database that have roles attached to them
        $groups = GiceGroup::query()->withWhereHas('roles')->whereIn('gice_groups.id', $groupIds)->get();

        // Get the list of the managed roles the user currently has assigned to them, in case we need to
        // remove any roles where the user is no longer a member of the linked group(s)
        $currentRoles = $user->roles()->withWhereHas('managedRoles')->get();

        // Get each role and assign them to the user
        $rolesToAdd = $groups->flatMap(fn ($group) => $group->roles)->unique('id');
        $user->assignRole($rolesToAdd);

        // If no groups were removed, skip processing
        if (blank($this->removedGroups)) {
            return;
        }

        // Loop through the list of current roles to see if they need to be removed or not
        foreach ($currentRoles as $role) {

            // Is the user still a member of a group linked to this role?
            if ($role->managedRoles->whereIn('group_id', $groupIds)->isNotEmpty()) {
                continue;
            }

            // List of managed role entities that are from a removed group
            $entities = $role->managedRoles->whereIn('group_id', $this->removedGroups);

            // If the role should be removed then remove it
            if ($entities->every('auto_remove_role', true)) {
                $user->removeRole($role);
            }
        }
    }

    /**
     * Trigger the job that fetches the user's characters from GICE.
     */
    protected function fetchCharacters(User $user): void
    {
        // Dispatch the job that gets characters from GICE
        FetchUserCharactersFromGice::dispatchSync($user);
    }
}
