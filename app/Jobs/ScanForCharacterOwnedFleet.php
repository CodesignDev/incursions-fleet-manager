<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Character;
use App\Models\Fleet;
use App\Models\FleetScan;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Support\Arr;

class ScanForCharacterOwnedFleet implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The character that is being checked to see if they are in a fleet.
     */
    protected Character $character;

    /**
     * Create a new job instance.
     */
    public function __construct(Character $character)
    {
        $this->character = $character->withoutRelations();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Skip if the character is blacklisted
        if ($this->character->onBlacklist) {
            return;
        }

        // Check if the system has a fleet where this character is a member
        if (Fleet::whereHas('members', fn (Builder $builder) => $builder->where('character_id', $this->character->id))->exists()) {
            return;
        }

        // Catch any ESI errors
        try {

            // Query ESI for the character's current fleet
            $response = Esi::defaultEsiVersion('dev')
                ->withUrlParameters(['character_id' => $this->character->id])
                ->get('/characters/{character_id}/fleet')
                ->throwUnlessStatus(404);

            // If the response was a 404, just exit
            if ($response->notFound()) {
                return;
            }

            // Get the fleet id and fleet boss id from the response
            $data = $response->json();
            $fleetId = Arr::get($data, 'fleet_id');
            $fleetBoss = Arr::get($data, 'fleet_boss_id');

            // Store the fleet found in the database
            FleetScan::updateOrCreate([
                'character_id' => $this->character->id,
                'fleet_id' => $fleetId,
            ], [
                'fleet_boss_id' => $fleetBoss,
            ]);
        }

        // Catch response errors
        catch (RequestException $e) {

            // Catch and ignore any 401 and 403 errors
            if (in_array($e->response->status(), ['401', '403'])) {
                return;
            }

            // Throw the exception up to be processed by the relevant middleware
            throw $e;
        }

        // Catch connection related errors and requeue the job
        catch (ConnectionException) {
            $this->release();
        }
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new SkipIfBatchCancelled];
    }
}
