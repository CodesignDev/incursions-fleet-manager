<?php

namespace App\Jobs;

use App\Models\Character;
use App\Models\GiceGroup;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class FetchCharacterOwnerInformation implements ShouldQueue
{
    use Queueable;

    /**
     * List of characters that to fetch owner information for.
     */
    protected array $characters;

    /**
     * Create a new job instance.
     */
    public function __construct(array|int $characters)
    {
        $this->characters = Arr::wrap($characters);
    }

    /**
     * Execute the job.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function handle(): void
    {
        // If there are no characters then exit
        if (blank($this->characters)) {
            return;
        }

        // Make a request to GICE for the accounts that own the requested characters
        $accounts = Http::gice()
            ->post('/api/pilot/accounts', $this->characters)
            ->throw()
            ->collect();

        // Get the list of characters -> owners and update the relevant characters with the
        // account id
        $accounts->each(
            fn($account) => Character::query()
                ->whereIn('id', Arr::get($account, 'characterIds', []))
                ->update(['user_id' => Arr::get($account, 'id')])
        );

        // Create any accounts that don't exist
        $accounts
            ->pipe(function (Collection $accounts) {
                $knownAccounts = User::whereIn('id', $accounts->pluck('id'))->get();
                return $accounts->reject(fn ($account) => $knownAccounts->contains(Arr::get($account, 'id')));
            })
            ->tap(function (Collection $accounts) {
                $groups = $accounts->pluck('primaryGroupId');
                $groups->each(fn ($group) => GiceGroup::firstOrCreate(['id' => $group]));
            })
            ->each(fn ($account) => tap(
                User::create(array_merge(
                    Arr::only($account, ['id', 'name']),
                    ['username' => str(Arr::get($account, 'name'))->lower()->replace("'", '')],
                )),
                function (User $user) use ($account) {
                    $user->giceGroups()->attach(Arr::get($account, 'primaryGroupId'));
                }
            ));
    }
}
