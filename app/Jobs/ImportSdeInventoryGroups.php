<?php

namespace App\Jobs;

use App\Models\SDE\Inventory\InventoryGroup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use League\Csv\Reader as CsvReader;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ImportSdeInventoryGroups implements ShouldQueue
{
    use Queueable;

    /**
     * The list of group ids to import.
     */
    protected array $groupIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array|int $groupIds = [])
    {
        $this->groupIds = Arr::wrap($groupIds);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Create a temporary directory to store the files for the current job
        $tmpDir = TemporaryDirectory::make()->deleteWhenDestroyed();

        // Make a request to fuzzworks to download the groups file
        $response = Http::sink($groupsCsv = $tmpDir->path('sde_groups.csv'))
            ->get('https://www.fuzzwork.co.uk/dump/latest/invGroups.csv');

        // If the response failed, just exit
        if (! $response->ok()) {
            return;
        }

        $groups = collect($this->groupIds);

        // Read the CSV file and create / update entries for each of the categories
        $reader = CsvReader::createFromPath($groupsCsv)->setHeaderOffset(0);

        // Iterate over the entries
        LazyCollection::make(static fn () => yield from $reader->getRecords())
            ->when($groups->isNotEmpty())
            ->filter(fn ($record) => $groups->contains($record['groupID']))
            ->each(function ($record) {
                InventoryGroup::updateOrCreate([
                    'group_id' => $record['groupID'],
                ], [
                    'category_id' => $record['categoryID'],
                    'name' => $record['groupName'],
                    'published' => $record['published'],
                ]);
            });
    }
}
