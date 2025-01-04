<?php

namespace App\Jobs;

use App\Enums\EveIdRange;
use App\Exceptions\InvalidEveIdRange;
use App\Jobs\Middleware\HandleSdeErrors;
use App\Models\Universe\Stargate;
use App\Traits\FetchesNamesFromSde;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchStargateInformation implements ShouldQueue
{
    use Batchable, FetchesNamesFromSde, Queueable;

    /**
     * The id of the stargate that we are fetching the data for.
     */
    protected int $stargateId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $stargateId)
    {
        $this->stargateId = $stargateId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // If no celestial or solar system id was passed, exit
        if (blank($this->stargateId)) {
            return;
        }

        // If the ID range is invalid, throw an exception and fail
        if (EveIdRange::isValidId($this->stargateId, EveIdRange::Stargates)) {
            throw (new InvalidEveIdRange())->withId($this->stargateId, EveIdRange::Stargates);
        }

        // Fetch the stargate information and name from the SDE
        $stargateInfo = Http::sde()
            ->withUrlParameters(['stargate_id' => $this->stargateId])
            ->get('/universe/stargates/{stargate_id}')
            ->throw()
            ->fluent();

        // Fetch the name of the stargate
        $stargateName = $this->fetchNameFromSde($this->stargateId, function () use ($stargateInfo) {

            // Fetch the system name and then build the name for the stargate
            $systemName = $this->fetchNameFromSde($stargateInfo->value('solarSystemID'), 'Unknown System');

            return "Stargate ($systemName)";
        });

        // Create the entry for the stargate
        tap(Stargate::create([
            'id' => $stargateInfo->value('stargateID', $this->stargateId),
            'system_id' => $stargateInfo->value('solarSystemID'),
            'type_id' => $stargateInfo->value('typeID'),
            'name' => $stargateName,
        ]), function (Stargate $stargate) use ($stargateInfo) {

            // Save the position of the stargate in space
            $stargate->setPosition(...$stargateInfo->value('position', []));

            // Create the connection between this stargate and the destination, although the data for the
            // destination may not exist yet
            $stargate->connection()->create([
                'destination_stargate_id' => $stargateInfo->value('destination'),
            ]);
        });
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new HandleSdeErrors];
    }
}
