<?php

namespace App\Services\Esi;

use App\Models\Character;
use App\Models\Corporation;
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
     * The character to authenticate the request with.
     */
    protected ?int $character = null;

    /**
     * The corporation to authenticate the request with.
     */
    protected ?int $corporation = null;

    /**
     * The default ESI version to use when none is specified.
     */
    protected string $defaultEsiVersion = 'latest';

    /**
     * Create a new HTTP Client instance.
     */
    public function __construct(?EsiHttpClientFactory $factory)
    {
        parent::__construct($factory);
    }

    /**
     * Set the proxy base URL for the pending request.
     */
    public function proxyBaseUrl(string $url): static
    {
        $this->proxyBaseUrl = $url;

        return $this;
    }

    /**
     * Set the proxy auth token for the pending request.
     */
    public function withProxyAuth(string $token, string $header = 'X-Proxy-Auth'): static
    {
        return tap($this, function () use ($token, $header) {
            $this->proxyAuthHeader = [$header => $token];
        });
    }

    /**
     * Whether to use the ESI proxy for this request.
     */
    public function useProxy(bool $useProxy = true): static
    {
        return tap($this, function () use ($useProxy) {
            $this->useProxy = $useProxy;
        });
    }

    /**
     * Send a public esi request instead of using the proxy.
     */
    public function public(): static
    {
        return $this->useProxy(false);
    }

    /**
     * Set a character to authenticate the proxied esi request with.
     */
    public function withCharacter(Character|int $character): static
    {
        return $this->withEntity('character', $character);
    }

    /**
     * Set a corporation to authenticate the proxied esi request with.
     */
    public function withCorporation(Corporation|int $corporation): static
    {
        return $this->withEntity('corporation', $corporation);
    }

    /**
     * Set the default ESI version to use when none is specified.
     */
    public function defaultEsiVersion(string $version): static
    {
        return tap($this, function () use ($version) {
            $this->defaultEsiVersion = $version;
        });
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
     * Use a character or corporation entity to authenticate the request with.
     */
    protected function withEntity(string $type, $entity): static
    {
        return tap($this, function () use ($type, $entity) {
            $entityId = match (true) {
                is_a($entity, Character::class), is_a($entity, Corporation::class) => $entity->getAttribute('id'),
                default => $entity,
            };

            $this->{$type} = $entityId;
        });
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

        // Add the proxy auth if it has been specified
        if (! empty($this->proxyAuthHeader)) {
            $this->withHeaders($this->proxyAuthHeader);
        }

        // Apply the entity to the headers
        $urlParameters = $this->urlParameters;

        $characterId = Arr::get($urlParameters, 'character_id', $this->character);
        $corporationId = Arr::get($urlParameters, 'corporation_id', $this->corporation);

        if (! is_null($characterId)) {
            $this->applyProxyEntityHeaders($characterId);
        } else if (! is_null($corporationId)) {
            $this->applyProxyEntityHeaders($corporationId);
        }
    }

    protected function applyProxyEntityHeaders(int $entityId, $type = 'P'): void
    {
        $this->withHeaders([
            'X-Entity-ID'  => $entityId,
            'X-Token-Type' => $type,
        ]);
    }
}
