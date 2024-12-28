<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FleetLink;
use App\Http\Controllers\Concerns\PollsBatches;
use App\Http\Controllers\Controller;
use App\Http\Requests\FleetLinkVerificationRequest;
use App\Jobs\VerifyFleetLinkIsAccessible;
use App\Models\Fleet;
use Illuminate\Bus\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class FleetLinkVerificationApiController extends Controller
{
    use PollsBatches;

    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function startVerification(FleetLinkVerificationRequest $request): JsonResponse
    {
        // Get the fleet id from the url
        $fleetLink = $request->validated('link');
        $fleetId = FleetLink::extractFleetIdFromLink($fleetLink);

        // If the fleet is already tracked, throw a validation error
        throw_if(
            Fleet::whereTracked()->whereEsiFleetId($fleetId)->exists(),
            ValidationException::withMessages([
                'link' => 'This fleet is already a tracked fleet.',
            ])
        );

        // Get the current user
        $user = $request->user();

        // Create the verification job that checks all the user's characters against the fleet link
        // to see if any can access the fleet.
        $batch = Bus::batch([new VerifyFleetLinkIsAccessible($user, $fleetLink)])
            ->then(
                // Clear the character cache
                fn () => cache()->forget('fleet-link-verification:'.$fleetId.':characters')
            )
            ->withOption('fleet', $fleetId) // Attach the fleet to the batch, so we can retrieve it later
            ->dispatch();

        return response()->json(['id' => $batch->id], 201);
    }

    public function checkProgress(string $verifyJobId): Response
    {
        // Poll the batch
        return $this->pollBatch(
            $verifyJobId,
            whenSuccessful: fn ($batch) => $this->handleJobSuccess($batch),
            whenFailed: fn () => response()->json(['error' => 'Failed'], 422),
            whenInProgress: fn () => response()->noContent(202),
            whenNotFound: fn () => response()->json(['error' => 'Not Found'], 404)
        );
    }

    public function cancel(string $verifyJobId): Response
    {
        // Find the batch with the passed id
        $batch = Bus::findBatch($verifyJobId);

        // Return 404 if no batch was found
        if (is_null($batch)) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        // If the batch has already finished, then return a 422
        if ($batch->finished()) {
            return response()->json(['error' => 'Already finished'], 422);
        }

        // Cancel the batch and return a 204
        $batch->cancel();
        return response()->noContent();
    }

    protected function handleJobSuccess(Batch $batch): Response
    {
        // Get the fleet id from the batch
        $fleetId = $batch->options['fleet'];

        if (is_null($fleetId)) {
            return response()->json(['error' => 'Invalid Data'], 400);
        }

        // Check if the fleet now exists from the check
        $validFleet = Fleet::whereEsiFleetId($fleetId)->exists();

        // Return the response
        return response()->json(['valid' => $validFleet]);
    }
}
