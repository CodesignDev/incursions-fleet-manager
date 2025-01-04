<?php

namespace App\Jobs;

use App\Models\Universe\NpcStationService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Fluent;

class FetchNpcStationServiceInformationFromSde implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The id of the station service to fetch the information for.
     */
    protected int $stationServiceId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $stationServiceId)
    {
        $this->stationServiceId = $stationServiceId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Make a request to the SDE to fetch the relevant information
        $data = rescue(fn () => Http::sde()
            ->withUrlParameters(['service_id' => $this->stationServiceId])
            ->get('/universe/stationServices/{service_id}')
            ->throw()
            ->fluent(), rescue: new Fluent());

        // Update or create the station service entry in the database
        NpcStationService::updateOrCreate(
            ['id' => $this->stationServiceId],
            [
                'name' => $data->get('serviceNameID.en'),
                'description' => $data->get('descriptionID.en'),
            ]
        );
    }
}
