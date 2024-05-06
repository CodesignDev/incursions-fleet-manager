<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Alliance;
use App\Models\AllianceStandings;
use App\Models\Character;
use App\Models\Corporation;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class FetchAllianceStandings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function handle(): void
    {
        // Get the list of standings from GICE
        $standings = Http::gice()
            ->get('/api/universe/standings')
            ->throw()
            ->collect();

        // Change the keys in the data to be snake_case
        $standings = $standings->map(fn ($entry) => Arr::mapWithKeys($entry, fn ($value, $key) => [Str::snake($key) => $value]));

        // Add GSF to the list of standings
        $gsfAllianceId = config('waitlist.alliance_id', 1354830081);
        $standings = $standings
            ->unless(fn ($collection) => $collection->has($gsfAllianceId))
            ->merge([$gsfAllianceId => ['contact_id' => $gsfAllianceId, 'contact_type' => 'A', 'standing' => 10]]);

        // Clamp the standing value to the values that EVE allows
        $standings = $standings->map(fn ($item) => Arr::update(
            $item,
            'standing',
            fn ($standing) => Number::clamp((float) $standing, -10, 10)
        ));

        // Mappings of the contact types to the actual entity model
        $mappings = [
            'A' => $this->getMorphAlias(Alliance::class),
            'C' => $this->getMorphAlias(Corporation::class),
            'P' => $this->getMorphAlias(Character::class),
        ];

        // Map the standings to the actual entity class
        $standingEntries = $standings
            ->map(fn ($entry) => array_merge($entry, ['contact_type' => $this->getContactMapping($entry, $mappings)]))
            ->where('contact_type', '!=', false)
            ->values();

        // Get the list of current standings
        $currentStandings = AllianceStandings::all();

        // Get the list of new entries
        $newEntries = $standingEntries->whereNotIn('contact_id', $currentStandings->pluck('contact_id'));
        $updatedEntries = $standingEntries
            ->filter()
            ->where(function ($entry) use ($currentStandings) {
                $currentEntry = $currentStandings->firstWhere('contact_id', data_get($entry, 'contact_id'));
                if (is_null($currentEntry)) {
                    return false;
                }

                return data_get($entry, 'contact_id') === $currentEntry->contact_id
                    && data_get($entry, 'standing') !== $currentEntry->standing;
            })
            ->pluck('standing', 'contact_id');

        // Add new entries to the database using upsert
        AllianceStandings::query()->upsert(
            $newEntries->all(),
            ['contact_id', 'contact_type'],
            ['standing']
        );

        // Update the standing value for any existing values
        AllianceStandings::whereIn('contact_id', $updatedEntries->keys())
            ->get()
            ->each(fn(AllianceStandings $entry) => $entry->update(
                ['standing' => $updatedEntries->get($entry->contact_id, 0)]
            ));

        // Delete any old standings that are no longer in the standings list
        AllianceStandings::all()
            ->reject(fn ($entry) => $standings->containsStrict('contact_id', $entry->contact_id))
            ->when(
                fn ($collection) => $collection->isNotEmpty(),
                fn ($collection) => $collection->toQuery()->withModelScopes()->delete()
            );

        // For the new entries, fetch the relevant information
        $newStandingsEntries = $standings->whereIn('contact_id', $newEntries->pluck('contact_id'));
        $newAlliances = $newStandingsEntries->where('contact_type', 'A')->pluck('contact_id');
        $newCorporations = $newStandingsEntries->where('contact_type', 'C')->pluck('contact_id');
        $newCharacters = $newStandingsEntries->where('contact_type', 'P')->pluck('contact_id');

        // Fetch the details for alliances and corporations
        FetchAllianceInformation::dispatchIf($newAlliances->isNotEmpty(), $newAlliances->all());
        FetchCorporationInformation::dispatchIf($newCorporations->isNotEmpty(), $newCorporations->all());

        // Fetch the details for missing characters and then their affiliation
        Bus::chain([
            new FetchMissingCharacterInformation($newCharacters->all()),
            new FetchCharacterAffiliation($newCharacters->all()),
        ])->dispatchIf($newCharacters->isNotEmpty());
    }

    private function getContactMapping(array $entry, array $mappings = []): string|false
    {
        // Get the contact type
        $type = data_get($entry, 'contact_type', 'X');
        if (array_key_exists($type, $mappings)) {
            return $mappings[$type];
        }

        // Try and figure out the contact type based on the ID
        $idRanges = collect([
            'A' => [
                [90_000_000, 98_000_000]
            ],
            'C' => [
                [98_000_000, 99_000_000]
            ],
            'P' => [
                [99_000_000, 100_000_000],
                [2_100_000_000, 2_147_483_647]
            ],
        ]);

        // Attempt to figure out the type from the id ranges
        $contactId = data_get($entry, 'contact_id');
        $typeFromRange = $idRanges->search(
            fn($ranges) => Arr::first($ranges, function ($range) use ($contactId) {
                [$min, $max] = $range;
                return $contactId >= $min && $contactId < $max;
            }, 'X')
        );
        if (array_key_exists($typeFromRange, $mappings)) {
            return $mappings[$typeFromRange];
        }

        // Use ESI to figure out the type of the contact
        try {
            $esiResult = Esi::public()->post('/universe/names', [$contactId])->json();
            $category = Arr::get(head($esiResult), 'category', 'unknown');

            $esiMappings = [
                'alliance' => 'A',
                'corporation' => 'C',
                'character' => 'P',
                'unknown' => 'X',
            ];

            $esiType = $esiMappings[$category];
            if (array_key_exists($esiType, $mappings)) {
                return $mappings[$esiType];
            }
        } catch (Exception) {
            //
        }

        return false;
    }

    private function getMorphAlias(string $class): string
    {
        return (new $class)->getMorphClass();
    }
}
