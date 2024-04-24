<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Character;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class FetchMissingCharacterInformation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of characters to fetch information for.
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
        // If there are no alliances to fetch, exit
        if (blank($this->characters)) {
            return;
        }

        // Find and remove any character ids that are already in the database
        $existingCharacters = Character::query()->whereIn('id', $this->characters)->get();
        $characterIds = collect($this->characters)->diff($existingCharacters->pluck('id'));
        if ($characterIds->isEmpty()) {
            return;
        }

        // Make esi requests to get both the name and affiliation of the characters
        $characterNames = Esi::public()->post('/universe/names', $characterIds)->throw()->collect();
        $characterAffiliation = Esi::public()->post('/characters/affiliation', $characterIds)->throw()->collect();

        // Merge the data together
        $characters = collect()
            ->merge($characterNames->where('category', 'character'))
            ->keyBy('id')
            ->map(fn ($data, $key) => array_merge($data, $characterAffiliation->firstWhere('character_id', $key)));

        // Update characters
        $characters
            ->map(fn ($data) => tap(new Character)->forceFill(
                Arr::only($data, ['id', 'name', 'corporation_id'])
            ))
            ->each->save();
    }
}
