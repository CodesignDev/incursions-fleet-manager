<?php

namespace App\Services\Esi;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PendingEsiRequest extends PendingRequest
{
    /**
     * The factory instance.
     *
     * @var \App\Services\Esi\EsiHttpClientFactory|null
     */
    protected $factory;

    /**
     * The base URL to use when sending requests to ESI when using the ESI proxy.
     */
    protected string $proxyBaseUrl;

    /**
     * The auth header to use when authenticating against the ESI proxy.
     *
     * @var array<string, string>
     */
    protected array $proxyAuthHeader;

    /**
     * Whether to use the ESI proxy to send requests to ESI.
     */
    protected bool $useProxy = false;

    /**
     * The default ESI version to use when none is specified.
     */
    protected string $defaultEsiVersion = 'latest';

    /**
     * Create a new HTTP Client instance.
     */
    public function __construct(?EsiHttpClientFactory $factory)
    {
//        $this->factory = $factory;

        parent::__construct($factory);
    }

    /**
     * Set the proxy base URL for the pending request.
     */
    public function proxyBaseUrl(string $url): PendingEsiRequest
    {
        $this->proxyBaseUrl = $url;

        return $this;
    }

    /**
     * Set the proxy auth token for the pending request.
     */
    public function withProxyAuth(string $token, string $header = 'X-Proxy-Auth'): PendingEsiRequest
    {
        $this->proxyAuthHeader = [$header => $token];

        return $this;
    }

    /**
     * Whether to use the ESI proxy for this request.
     */
    public function useProxy(bool $useProxy = true): PendingEsiRequest
    {
        $this->useProxy = $useProxy;
        return $this;
    }

    /**
     * Send a public esi request instead of using the proxy.
     */
    public function public(): PendingEsiRequest
    {
        return $this->useProxy(false);
    }

    /**
     * Set the default ESI version to use when none is specified.
     */
    public function defaultEsiVersion(string $version): PendingEsiRequest
    {
        $this->defaultEsiVersion = $version;

        return $this;
    }

    /**
     * Send the request to the given URL.
     *
     * @throws Exception
     */
    public function send(string $method, string $url, array $options = []): Response
    {
        // If the proxy is being used, apply the correct base url and headers
        if ($this->useProxy) {
            $this->baseUrl = $this->proxyBaseUrl;
            $this->setProxyHeaders();
        }

        // Apply default esi version if none specified
        $url = $this->parseEsiVersion($url);

        return parent::send($method, $url, $options);
    }

    /**
     * Parse the given url and check if the required api version needs adding.
     */
    protected function parseEsiVersion(string $url): string
    {
        // If the url starts with http/https, then return the url as is
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        // Default ESI versions are v1-v6 / latest / dev / legacy
        $defaultVersions = Collection::range(1, 6)
            ->map(fn ($v) => Str::before($v, 'v'))
            ->merge(['latest', 'dev', 'legacy']);

        $defaultVersionPatterns = $defaultVersions->map(fn ($version) => '/^\/('.$version.')\//');

        // If the url already contains a version, return the url
        if (Str::isMatch($defaultVersionPatterns, $url)) {
            return $url;
        }

        // Get the default version (or use latest if not set)
        $defaultVersion = $this->defaultEsiVersion ?: 'latest';

        // Validate the version against the list of default versions
        if (! $defaultVersions->contains($defaultVersion)) {
            $defaultVersion = 'latest';
        }

        // Format the url
        return (string) Str::of($url)
            ->start('/')
            ->prepend($defaultVersion)
            ->start('/');
    }

    /**
     * Set the required headers for the ESI proxy based on the url parameters passed to the request.
     */
    protected function setProxyHeaders(): void
    {
        if (! $this->useProxy) {
            return;
        }

        if (! empty($this->proxyAuthHeader)) {
            $this->withHeaders($this->proxyAuthHeader);
        }

        $urlParameters = $this->urlParameters;

        if (Arr::exists($urlParameters, 'character_id')) {
            $this->withHeaders([
                'X-Entity-ID'  => $urlParameters['character_id'],
                'X-Token-Type' => 'P',
            ]);
        }

        if (Arr::exists($urlParameters, 'corporation_id')) {
            $this->withHeaders([
                'X-Entity-ID'  => $urlParameters['corporation_id'],
                'X-Token-Type' => 'C',
            ]);
        }
    }
}
