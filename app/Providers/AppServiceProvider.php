<?php

namespace App\Providers;

use App\Auth\GiceSocialiteProvider;
use App\Macros\EventEveDowntimeMixin;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register socialite providers
        $this->app->afterResolving(SocialiteFactory::class, function ($socialite) {
            $socialite->extend('gice', function ($app) use ($socialite) {
                $config = $app['config']['services.gice'];
                return $socialite->buildProvider(GiceSocialiteProvider::class, $config);
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register any macros
        $this->bootMacros();

        // Register GICE HTTP api client
        $this->registerGiceApiClient();
    }

    private function bootMacros(): void
    {
        // Register a helper for creating unsigned non-incrementing ids
        Blueprint::macro('staticId', function ($column = 'id'): ColumnDefinition {
            return $this->unsignedBigInteger($column)->primary();
        });
        Blueprint::macro('uuidId', function ($column = 'id'): ColumnDefinition {
            return $this->uuid($column)->primary();
        });

        // Event macro that defines a daily schedule at EVE downtime
        Event::mixin(new EventEveDowntimeMixin());
    }

    /**
     * Register the GICE client
     */
    private function registerGiceApiClient(): void
    {
        // Create a macro to pre-configure the HTTP client for GICE api requests
        Http::macro('gice', function (): PendingRequest {

            // Create request
            $request = Http::asJson();

            // Configure the base url
            $giceHost = config('gice.host', 'gice.goonfleet.com');
            $gicePort = config('gice.port', 443);
            $giceScheme = config('gice.scheme', 'https');
            $giceBaseUrl = rtrim(sprintf('%s://%s:%s', $giceScheme, $giceHost, $gicePort), '/');
            $request->baseUrl($giceBaseUrl);

            // Add the required authentication
            $clientId = config('gice.client_id');
            $clientSecret = config('gice.client_secret');
            $request->withBasicAuth($clientId, $clientSecret);

            return $request;
        });
    }
}
