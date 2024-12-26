<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Alliance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;

class FetchAllianceInformation implements ShouldQueue
{
    use Queueable;

    /**
     * The list of alliances to fetch information for.
     */
    protected array $alliances;

    /**
     * Create a new job instance.
     */
    public function __construct(array|int $alliances)
    {
        $this->alliances = Arr::wrap($alliances);
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
        if (blank($this->alliances)) {
            return;
        }

        // Loop through each alliance in the list and fetch its information from ESI
        foreach ($this->alliances as $alliance) {

            // Fetch the corp data from ESI
            $allianceData = Esi::public()
                ->withUrlParameters(['alliance_id' => $alliance])
                ->get('/alliances/{alliance_id}')
                ->throw()
                ->json();

            // Get only the required information from the data
            Alliance::query()->updateOrCreate(
                ['id' => $alliance],
                Arr::only($allianceData, ['name', 'ticker'])
            );
        }
    }
}
