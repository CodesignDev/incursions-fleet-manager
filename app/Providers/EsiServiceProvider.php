<?php

namespace App\Providers;

use App\Services\Esi\EsiHttpClientFactory;
use App\Services\Esi\PendingEsiRequest;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class EsiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the esi client factory
        $this->callAfterResolving(EsiHttpClientFactory::class, function ($factory) {

            // Apply default options to the request
            $factory->withDefaultOptions(function (PendingEsiRequest $request) {

                // Default configuration
                $request->asJson();

                // Get ESI host details
                $esiHost = config('esi.host', 'esi.evetech.net');
                $esiPort = config('esi.port', 443);
                $esiScheme = config('esi.scheme', 'https');
                $esiBaseUrl = rtrim(sprintf('%s://%s:%s', $esiScheme, $esiHost, $esiPort), '/');

                // Get ESI proxy details
                $esiProxyHost = config('esi.proxy_host', '');
                $esiProxyPort = config('esi.proxy_port', '');
                $esiProxyScheme = config('esi.proxy_scheme', '');
                $esiProxyPath = config('esi.proxy_path', '');

                // Token to authenticate to the proxy
                $esiProxyToken = config('esi.proxy_token', '');

                // Get the user agent to send with each ESI request
                $userAgent = config('esi.user_agent', 'Incursion Fleet Manager');
                $userAgentContact = config('esi.user_agent_contact');
                if (! empty($userAgentContact)) {
                    $userAgent .= sprintf(' (Contact: %s)', $userAgentContact);
                }

                // Set values onto the request
                $request->baseUrl($esiBaseUrl);
                $request->proxyBaseUrl('');
                $request->withUserAgent($userAgent);

                // If the proxy host has been specified, configure elements of the proxy
                if (! empty($esiProxyHost)) {
                    $esiProxyBaseUrl = rtrim(
                        sprintf(
                            '%s://%s:%s/%s',
                            $esiProxyScheme,
                            $esiProxyHost,
                            $esiProxyPort,
                            ltrim($esiProxyPath, '/')
                        ),
                        '/'
                    );

                    $request->withProxyAuth($esiProxyToken);
                    $request->proxyBaseUrl($esiProxyBaseUrl);
                    $request->useProxy();
                }
            });
        });
    }
}
