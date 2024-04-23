<?php

namespace App\Jobs;

use App\Models\Character;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class FetchUserCharactersFromGice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user whose characters are being fetched.
     */
    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        // Store the user without any relations
        $this->user = $user->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function handle(): void
    {
        // Get the user id of the current user
        $userId = $this->user->id;

        // Make a request to GICE
        $giceCharacters = Http::gice()
            ->withUrlParameters(['user_id' => $userId])
            ->get('/api/account/{user_id}/pilots')
            ->throw()
            ->collect();

        // Get the list of characters currently attached the user
        /** @var \Illuminate\Database\Eloquent\Collection $currentUserCharacters */
        $currentUserCharacters = Character::where('user_id', $userId)->get();

        // Get the list of known and unknown characters
        /** @var \Illuminate\Database\Eloquent\Collection $knownCharacters */
        $knownCharacters = Character::whereIn('id', $giceCharacters->pluck('id'))->get();
        $unknownCharacters = $giceCharacters->whereNotIn('id', $knownCharacters->pluck('id'));

        // Map unknown characters into a character model
        $newCharacters = $unknownCharacters->mapInto(Character::class);

        // Characters that are known that need assigning to the user
        $updatedCharacters = $knownCharacters->where('user_id', '!=', $userId);

        // Remove any characters from the user's current characters
        $removedCharacters = $currentUserCharacters->except(
            $giceCharacters->pluck('id')->all()
        );

        // Add new characters to the user
        $this->user->characters()->saveMany($newCharacters);

        // Sync any existing character changes
        $updatedCharacters->each(fn ($character) => $character->user()->associate($this->user)->save());
        $removedCharacters->each(fn ($character) => $character->user()->disassociate()->save());
    }

    /**
     * Determine number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 3;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 5, 10];
    }
}
