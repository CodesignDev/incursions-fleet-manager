<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Bus;
use Throwable;

trait PollsBatches
{
    /**
     * Query the requested batch and return values relevant to the current batch status
     *
     * @param string $batchId
     * @param $whenSuccessful
     * @param $whenFailed
     * @param $whenInProgress
     * @param $whenNotFound
     *
     * @return mixed|null
     */
    protected function pollBatch(string $batchId, $whenSuccessful = null, $whenFailed = null, $whenInProgress = null, $whenNotFound = null)
    {
        // Load the batch
        $batch = Bus::findBatch($batchId);

        try {

            // Batch not found
            if (is_null($batch)) {
                return value($whenNotFound, $batchId);
            }

            // Batch is currently in progress
            if (! $batch->finished() && !($batch->hasFailures() && !$batch->allowsFailures())) {
                return ! is_null($whenInProgress)
                    ? value($whenInProgress, $batch)
                    : null;
            }

            // Batch has failed
            if ($batch->hasFailures()) {
                return ! is_null($whenFailed)
                    ? value($whenFailed, $batch)
                    : false;
            }

            // Otherwise the batch was successful
            return ! is_null($whenSuccessful)
                ? value($whenSuccessful, $batch)
                : true;

        } catch (Throwable $e) {
            if (function_exists('report')) {
                report($e);
            }
        }

        return null;
    }
}
