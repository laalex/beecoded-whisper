<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Socialite providers
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('hubspot', \SocialiteProviders\HubSpot\Provider::class);
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        });
    }
}
