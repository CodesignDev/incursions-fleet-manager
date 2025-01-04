<?php

namespace App\Jobs;

use App\Models\SDE\InventoryType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use League\Csv\Reader as CsvReader;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ImportSdeInventoryTypes implements ShouldQueue
{
    use Queueable;

    /**
     * The list of type ids to import.
     */
    protected array $typeIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array|int $typeIds = [])
    {
        $this->typeIds = Arr::wrap($typeIds);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Create a temporary directory to store the files for the current job
        $tmpDir = TemporaryDirectory::make()->deleteWhenDestroyed();

        // Make a request to fuzzworks to download the types file
        $response = Http::sink($typesCsv = $tmpDir->path('sde_types.csv'))
            ->get('https://www.fuzzwork.co.uk/dump/latest/invTypes.csv');

        // If the response failed, just exit
        if (! $response->ok()) {
            return;
        }

        $types = collect($this->typeIds);

        // Read the CSV file and create / update entries for each of the categories
        $reader = CsvReader::createFromPath($typesCsv)->setHeaderOffset(0);

        // Iterate over the entries
        LazyCollection::make(static fn () => yield from $reader->getRecords())
            ->when($types->isNotEmpty())
            ->filter(fn ($record) => $types->contains($record['typeID']))
            ->map(fn ($record) => Arr::map($record, fn ($value) => $value !== 'None' ? $value : null))
            ->each(function ($record) {
                InventoryType::updateOrCreate([
                    'id' => $record['typeID'],
                ], [
                    'group_id' => $record['groupID'],
                    'market_group_id' => $record['marketGroupID'],
                    'race_id' => $record['raceID'],
                    'name' => $record['typeName'],
                    'description' => $record['description'],
                    'published' => $record['published'],
                    'volume' => $record['volume'],
                ]);
            });
    }
}
