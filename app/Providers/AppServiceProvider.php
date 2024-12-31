<?php

namespace App\Providers;

use App\Auth\GiceSocialiteProvider;
use App\Macros\BlueprintMixin;
use App\Macros\EventEveDowntimeMixin;
use App\Macros\InertiaMixin;
use App\Models\FleetInvite;
use App\Models\FleetMember;
use App\Models\Universe\SolarSystem;
use App\Models\WaitlistEntry;
use App\Observers\FleetInviteStateObserver;
use App\Observers\FleetMemberInviteObserver;
use App\Observers\SolarSystemInfoObserver;
use App\Observers\WaitlistEntryObserver;
use App\Services\Inertia\ZiggyHttpGateway;
use ArrayAccess;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Inertia\ResponseFactory as Inertia;
use Inertia\Ssr\Gateway;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use ReflectionException;
use Romans\Filter\IntToRoman;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Use a custom http gateway for Inertia SSR requests
        $this->app->bind(Gateway::class, ZiggyHttpGateway::class);

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
        // Boot any services required
        $this->bootMacros();
        $this->bootObservers();

        // Register GICE HTTP api client
        $this->registerGiceApiClient();
        $this->registerSDEApiClient();

        // Disable wrapping of API resources
        JsonResource::withoutWrapping();

        // Enable strict mode for models
        Model::shouldBeStrict();
    }

    /**
     * Register any class macros.
     */
    private function bootMacros(): void
    {
        // Register class mixins
        try {
            Blueprint::mixin(new BlueprintMixin());
            Event::mixin(new EventEveDowntimeMixin());
            Inertia::mixin(new InertiaMixin());
        } catch (ReflectionException) {}

        // Register an Arr helper to update a value
        Arr::macro('update', function (array|ArrayAccess &$array, int|null|string $key, callable $update, $default = null): array {
            $existingValue = Arr::get($array, $key, $default);
            $updatedValue = value($update, $existingValue);

            return Arr::set($array, $key, $updatedValue);
        });

        // Register a macro on the eloquent builder to pull in the model's scopes
        Builder::macro('withModelScopes', function (): static {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            if (is_null($this->getModel())) {
                return $this;
            }

            return $this->getModel()->registerGlobalScopes($this);
        });

        // Register a macro on Number to convert a number ro a roman numeral
        Number::macro('toRomanNumeral', function (int $number): string {
            $number = max(0, $number);

            return (new IntToRoman)->filter($number);
        });
    }

    /**
     * Register any model observers.
     */
    private function bootObservers(): void
    {
        // Register the relevant observers
        FleetInvite::observe(FleetInviteStateObserver::class);
        FleetMember::observe(FleetMemberInviteObserver::class);
        WaitlistEntry::observe(WaitlistEntryObserver::class);

        // Register listeners to the universe models
        SolarSystem::observe(SolarSystemInfoObserver::class);
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

    /**
     * Register the SDE client
     */
    private function registerSDEApiClient(): void
    {
        // Create a macro to pre-configure the HTTP client for SDE api requests
        Http::macro('sde', function (): PendingRequest {

            // Create request
            $request = Http::asJson();

            // Configure the base url
            $sdeHost = config('sde.host', 'sde.jita.space');
            $sdePort = config('sde.port', 443);
            $sdeScheme = config('sde.scheme', 'https');
            $sdeVersion = config('sde.version', 'latest');
            $sdeBaseUrl = rtrim(sprintf('%s://%s:%s/%s', $sdeScheme, $sdeHost, $sdePort, $sdeVersion), '/');
            $request->baseUrl($sdeBaseUrl);

            return $request;
        });
    }
}
