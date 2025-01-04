<?php

namespace App\Console\Commands;

use App\Jobs\ImportSdeInventoryCategories;
use App\Jobs\ImportSdeInventoryGroups;
use App\Jobs\ImportSdeInventoryTypes;
use App\Models\SDE\InventoryCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportSdeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sde:import
                            {--category=* : The category of inventory types to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import parts of the SDE into the application.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // If no input was specified, then just exit
        if (blank($requestedCategories = $this->option('category'))) {
            return;
        }

        // If we have no sde categories stored, import them
        if (InventoryCategory::doesntExist()) {

            $this->output->write('Pre-fetching SDE Categories...');
            dispatch_sync(new ImportSdeInventoryCategories());
            $this->output->writeln([' Done!', '']);
        }

        // Loop through each category and process the groups and types for each
        $categories = collect($requestedCategories)
            ->flatMap(fn ($category) => when(! is_numeric($category), fn () => [Str::singular($category), Str::plural($category)]))
            ->map(function ($value) {

                // Return the category id if the value is a number
                if (is_numeric($value)) {
                    return transform(InventoryCategory::firstWhere('id', $value), fn ($category) => [$value => $category]);
                }

                // Otherwise search for the category
                return InventoryCategory::query()
                    ->whereLike('name', Str::lower($value), caseSensitive: false)
                    ->pluck('name', 'id')
                    ->toArray();
            })
            ->filter(fn ($value) => filled($value) && Collection::wrap($value)->filter()->isNotEmpty())
            ->mapWithKeys(fn ($value, $key) => is_array($value) ? $value : [$key => $value]);

        // Process each category
        $categories->each(function ($category, $categoryId) {
            $category = Str::plural($category);


            // Process Groups

            // Get the list of groups in this category from everef
            $this->output->write(sprintf('Fetching Groups for the %s category...', $category));
            $groups = $this->makeEveRefRequest('/categories/{category_id}', ['category_id' => $categoryId], 'group_ids', []);

            // If no groups were found, skip this category
            if (blank($groups)) {
                $this->output->writeln(' Skipped!');
                $this->warn(sprintf('No groups were found for the %s category.', $category));
                return;
            }
            $this->output->writeln(' Done!');

            // Import the data for the required groups using an import job
            $this->output->write(sprintf('Processing %d groups for the %s category...', count($groups), $category));
            dispatch_sync(new ImportSdeInventoryGroups($groups));
            $this->output->writeln([' Done!', '']);


            // Process Types

            // Collect the list of types for these groups
            $this->output->write(sprintf('Fetching Types for the %s category...', $category));
            $types = collect($groups)
                ->flatMap(fn ($group) => $this->makeEveRefRequest('/groups/{group_id}', ['group_id' => $group], 'type_ids', []));

            // If no types were found, skip this category
            if (blank($types)) {
                $this->output->writeln(' Skipped!');
                $this->warn(sprintf('No types were found for the %s category.', $category));
                return;
            }
            $this->output->writeln(' Done!');

            // Import the data for the required groups using an import job
            $this->output->write(sprintf('Processing %d types for the %s category...', $types->count(), $category));
            dispatch_sync(new ImportSdeInventoryTypes($types->toArray()));
            $this->output->writeln([' Done!', '']);
        });

        // Print a final message
        $this->line('Import finished.');
    }

    private function makeEveRefRequest(string $url, array $parameters = [], string $key = null, $default = null)
    {
        return rescue(
            fn () => Http::asJson()
                ->withUrlParameters($parameters)
                ->baseUrl('https://ref-data.everef.net')
                ->get($url)
                ->throw()
                ->json($key, $default),
            rescue: $default,
            report: false
        );
    }
}
