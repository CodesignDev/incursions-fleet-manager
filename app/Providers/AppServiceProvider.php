<?php

namespace App\Providers;

use App\Auth\GiceSocialiteProvider;
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
        //
    }
}
