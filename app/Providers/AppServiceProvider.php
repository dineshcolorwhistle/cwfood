<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;
use App\Models\Client_contact;
use App\Observers\ClientContactObserver;
// Stripe
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind StripeClient as a singleton so you can type-hint it via DI
        $this->app->singleton(StripeClient::class, function () {
            // Reads STRIPE_SECRET from config('services.stripe.secret')
            return new StripeClient(config('services.stripe.secret'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1) Attach your model observer
        Client_contact::observe(ClientContactObserver::class);

        // 2) Register the Cognito driver with SocialiteProviders
        Event::listen(
            SocialiteWasCalled::class,
            [\SocialiteProviders\Cognito\CognitoExtendSocialite::class, 'handle']
        );
    }
}

