<?php

namespace App\Jobs;

use App\Models\Fleet;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class EsiFleetJob implements ShouldQueue
{
    /**
     * The fleet which is being processed by this job.
     *
     * @var \App\Models\Fleet
     */
    protected Fleet $fleet;

    /**
     * The list of relations to be included with the fleet.
     *
     * @var string[]
     */
    protected array $includedRelations = ['boss'];

    /**
     * The list of relations that are to be eager loaded.
     *
     * @var string[]
     */
    protected array $relationsToLoad;

    /**
     * Create a new job instance.
     */
    public function __construct(Fleet $fleet)
    {
        // Ensure the relations objects are set correctly
        $this->includedRelations ??= [];

        // Ensure the required relation(s) are loaded on the fleet instance
        $this->fleet = tap($fleet->withoutRelations(), function (Fleet $fleetInstance) use ($fleet) {

            // Copy relations from the original instance
            $relations = array_keys($fleet->getRelations());
            foreach (array_intersect($this->includedRelations, $relations) as $relation) {
                $fleetInstance->setRelation($relation, $fleet->getRelation($relation));
            }

            // Load any missing relations
            $relationsToEagerLoad = array_diff($this->relationsToLoad ?? $this->includedRelations, $relations);
            if (filled($relationsToEagerLoad)) {
                $fleetInstance->load($relationsToEagerLoad);
            }
        });
    }

    /**
     * Execute the job.
     */
    abstract public function handle(): void;

    /**
     * Fire the background job to attempt to locate the new fleet boss.
     */
    protected function locateFleetBoss(Fleet $fleet): void
    {
        // Attempt to find the fleet boss for this fleet
        LocateFleetBoss::dispatch($fleet);
    }
}
