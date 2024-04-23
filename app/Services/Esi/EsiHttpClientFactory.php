<?php

namespace App\Services\Esi;

use Illuminate\Http\Client\Factory as HttpClientFactory;

class EsiHttpClientFactory extends HttpClientFactory
{
    /**
     * Callback to configure the ESI client with the required settings.
     *
     * @var callable<\Illuminate\Http\Client\PendingRequest>|null
     */
    protected $esiConfigurationCallback;

    /**
     * A flag to declare that the pre-configured esi configuration should
     * not be applied for this request.
     */
    protected bool $ignoreDefaultEsiConfiguration = false;

    /**
     * Do not apply the pre-configured esi configuration.
     */
    public function ignoreDefaultOptions(): EsiHttpClientFactory
    {
        $this->ignoreDefaultEsiConfiguration = true;

        return $this;
    }

    /**
     * Configure the default configuration to apply to the ESI client.
     *
     * @var callable<\Illuminate\Http\Client\PendingRequest>|null  $callback
     */
    public function withDefaultOptions(?callable $callback)
    {
        $this->esiConfigurationCallback = $callback;

        return $this;
    }

    /**
     * Create a new pending request instance for this factory.
     */
    protected function newPendingRequest(): PendingEsiRequest
    {
        return new PendingEsiRequest($this);
    }

    /**
     * Execute a method against a new pending request instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if ($this->esiConfigurationCallback && !$this->ignoreDefaultEsiConfiguration) {
            return tap($this->newPendingRequest(), function ($request) {
                with($request, $this->esiConfigurationCallback);
                $request->stub($this->stubCallbacks)->preventStrayRequests($this->preventStrayRequests);
            })->{$method}(...$parameters);
        }

        return parent::__call($method, $parameters);
    }
}
