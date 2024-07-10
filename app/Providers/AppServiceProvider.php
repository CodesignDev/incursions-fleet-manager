<?php

namespace App\Providers;

use App\Auth\GiceSocialiteProvider;
use App\Macros\EventEveDowntimeMixin;
use App\Models\FleetInvite;
use App\Models\FleetMember;
use App\Models\WaitlistEntry;
use App\Observers\FleetInviteStateObserver;
use App\Observers\FleetMemberInviteObserver;
use App\Observers\WaitlistEntryObserver;
use App\Services\Inertia\ZiggyHttpGateway;
use ArrayAccess;
use Closure;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Inertia\LazyProp;
use Inertia\Ssr\Gateway;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

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
        // Register an Arr helper to update a value
        Arr::macro('update', function (array|ArrayAccess &$array, int|null|string $key, callable $update, $default = null): array {
            $existingValue = Arr::get($array, $key, $default);
            $updatedValue = value($update, $existingValue);

            return Arr::set($array, $key, $updatedValue);
        });

        // Register a helper for creating unsigned non-incrementing ids
        Blueprint::macro('staticId', function ($column = 'id'): ColumnDefinition {
            return $this->unsignedBigInteger($column)->primary();
        });
        Blueprint::macro('uuidId', function ($column = 'id'): ColumnDefinition {
            return $this->uuid($column)->primary();
        });

        // Register a macro on the eloquent builder to pull in the model's scopes
        Builder::macro('withModelScopes', function (): static {
            if (is_null($this->model)) {
                return $this;
            }

            return $this->model->registerGlobalScopes($this);
        });

        // Event macro that defines a daily schedule at EVE downtime
        Event::mixin(new EventEveDowntimeMixin());

        // Inertia macros
        Inertia::macro('lazyIf', function (Closure|bool $value, callable $callback): callable|LazyProp {
            return value($value) ? $this->lazy($callback): $callback;
        });
        Inertia::macro('lazyUnless', function (Closure|bool $value, callable $callback): callable|LazyProp {
            return value($value) ? $callback : $this->lazy($callback);
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
