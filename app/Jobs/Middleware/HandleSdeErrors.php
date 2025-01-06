<?php

namespace App\Jobs\Middleware;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class HandleSdeErrors
{
    /**
     * Process the queued job.
     *
     * @param  mixed  $job
     * @param  \Closure(object): void  $next
     *
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function handle(object $job, Closure $next): void
    {
        try {
            $next($job);
        }

        // HTTP related errors... handle particular errors
        catch (RequestException $e) {

            // If we got a 404 response from SDE, just quit the job since the item doesn't exist.
            // This *shouldn't* happen since there should be data for an ID but it could be that
            // the wrong type of item was queried.
            if ($e->response->notFound()) {
                report($e);
                return;
            }

            // Requeue if we encounter any server errors
            if ($e->response->serverError()) {
                $job->release(30);
                return;
            }

            // Requeue the failed job after a minute
            $job->release(60);
        }

        // Connection errors... just requeue the job
        catch (ConnectionException) {
            $job->release(15);
        }
    }
}
