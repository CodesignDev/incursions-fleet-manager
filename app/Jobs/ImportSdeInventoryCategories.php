<?php

namespace App\Jobs;

use App\Models\SDE\Inventory\InventoryCategory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use League\Csv\Reader as CsvReader;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ImportSdeInventoryCategories implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Create a temporary directory to store the files for the current job
        $tmpDir = TemporaryDirectory::make()->deleteWhenDestroyed();

        // Make a request to fuzzworks to download the invCategories file
        $response = Http::sink($categoriesCsv = $tmpDir->path('sde_categories.csv'))
            ->get('https://www.fuzzwork.co.uk/dump/latest/invCategories.csv');

        // If the response failed, just exit
        if (! $response->ok()) {
            return;
        }

        // Read the CSV file and create / update entries for each of the categories
        $reader = CsvReader::createFromPath($categoriesCsv)
            ->setHeaderOffset(0);

        // Iterate over the entries
        LazyCollection::make(static fn () => yield from $reader->getRecords())
            ->each(function ($record) {
                InventoryCategory::updateOrCreate([
                    'category_id' => $record['categoryID'],
                ], [
                    'name' => $record['categoryName'],
                    'published' => $record['published'],
                ]);
            });
    }
}
