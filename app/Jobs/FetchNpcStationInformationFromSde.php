<?php

namespace App\Jobs;

use App\Enums\EveIdRange;
use App\Exceptions\InvalidEveIdRange;
use App\Models\Corporation;
use App\Models\Universe\NpcStation;
use App\Models\Universe\NpcStationOperation;
use App\Models\Universe\NpcStationService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class FetchNpcStationInformationFromSde implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Queueable;

    /**
     * The id of the npc station that we are fetching the data for.
     */
    protected int $stationId;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     * @noinspection PhpUnused
     */
    public int $uniqueFor = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(int $stationId)
    {
        $this->stationId = $stationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // If no station id was provided, skip
        if (blank($this->stationId)) {
            return;
        }

        // If the ID range is invalid, throw an exception and fail
        if (EveIdRange::isValidId($this->stationId, EveIdRange::Stations)) {
            throw (new InvalidEveIdRange())->withId($this->stationId, EveIdRange::Stations);
        }

        try {

            // Query the entity from the SDE
            $stationData = Http::sde()
                ->withUrlParameters(['station_id' => $this->stationId])
                ->get('/universe/stations/{station_id}')
                ->throw()
                ->fluent();

            // Create the npc station entry
            tap(NpcStation::updateOrCreate(
                [
                    'id' => $stationData->value('stationID', $this->stationId),
                    'system_id' => $stationData->value('solarSystemID'),
                ],
                [
                    'name' => $stationData->value('stationName'),
                    'type_id' => $stationData->value('stationTypeID'),
                    'operation_id' => $stationData->value('operationID'),
                    'corporation_id' => $stationData->value('corporationID'),
                ]
            ), function (NpcStation $station) use ($stationData) {
                $station->setPosition(...$stationData->only(['x', 'y', 'z']));
                $station->setMeta('office_rental_cost', $stationData->value('officeRentalCost', 0));
            });

            // Fetch the information on the operation this station does
            $operationData = Http::sde()
                ->withUrlParameters(['operation_id' => $stationData->value('operationID')])
                ->get('/universe/stationOperations/{operation_id}')
                ->throw()
                ->fluent();

            // Create the station operation entry
            tap(NpcStationOperation::updateOrCreate(
                [
                    'id' => $operationData->value('stationOperationID'),
                ],
                [
                    'name' => $operationData->get('operationNameID.en'),
                    'description' => $operationData->get('descriptionID.en'),
                ]
            ), function (NpcStationOperation $operation) use ($operationData) {
                $services = $operationData->collect('services');

                // Create any missing services
                $services
                    ->map(fn ($service) => NpcStationService::firstOrCreate(
                        ['id' => $service],
                        ['name' => 'Unknown Station Service '.$service]
                    ))

                    // Dispatch fetch jobs for each of the new services
                    ->tap(function (Collection $services) {
                        $stationServiceFetchJobs = $services
                            ->filter(fn (NpcStationService $service) => $service->wasRecentlyCreated)
                            ->map(fn (NpcStationService $service) => new FetchNpcStationServiceInformationFromSde($service->id));

                        if ($this->batching()) {
                            $this->batch()?->add($stationServiceFetchJobs);
                            return;
                        }

                        $this->chain($stationServiceFetchJobs);
                    });

                // Attach the services to the operation
                $operation->services()->sync($services);

            });

            // Fetch the info for the corporation that owns this station if it doesn't exist
            if (Corporation::whereId($stationData->value('corporationID'))->doesntExist()) {
                dispatch(new FetchCorporationInformation($stationData->value('corporationID')));
            }
        }

        // HTTP related errors... handle particular errors
        catch (RequestException $e) {

            // Requeue if we encounter any server errors
            if ($e->response->serverError()) {
                $this->release();
            }

            // If the item is not found (which shouldn't happen), just exit but report the exception
            if ($e->response->notFound()) {
                report($e);
                return;
            }

            // Otherwise throw the exception up
            throw $e;
        }

        // Connection errors... just requeue the job
        catch (ConnectionException) {
            $this->release();
        }
    }

    /**
     * Get the unique ID for the job.
     *
     * @noinspection PhpUnused
     */
    public function uniqueId(): string
    {
        return 'station.'.$this->stationId;
    }
}
