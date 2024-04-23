<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Corporation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class FetchCorporationInformation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of corporations to fetch information for.
     */
    protected array $corporations;

    /**
     * Create a new job instance.
     */
    public function __construct(array|int $corporations)
    {
        $this->corporations = Arr::wrap($corporations);
    }

    /**
     * Execute the job.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function handle(): void
    {
        // If there are no corporations to fetch, exit
        if (blank($this->corporations)) {
            return;
        }

        // Loop through each corporation in the list and fetch its information from ESI
        foreach ($this->corporations as $corporation) {

            // Fetch the corp data from ESI
            $corporationData = Esi::public()
                ->withUrlParameters(['corporation_id' => $corporation])
                ->get('/corporations/{corporation_id}')
                ->throw()
                ->json();

            // Get only the required information from the data
            Corporation::query()->updateOrCreate(
                ['id' => $corporation],
                Arr::only($corporationData, ['name', 'ticker', 'tax_rate', 'alliance_id'])
            );
        }
    }
}
