<?php

namespace SocialiteProviders\Manager;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Socialite\SocialiteServiceProvider;

class ServiceProvider extends SocialiteServiceProvider
{
    /**
     * @param Dispatcher         $event
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function boot(Dispatcher $event, SocialiteWasCalled $socialiteWasCalled)
    {
        $event->fire($socialiteWasCalled);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(\SocialiteProviders\Contracts\Factory::class, function ($app) {
            return new SocialiteProvidersManager($app);
        });
    }

    public function provides()
    {
        return [\Laravel\Socialite\Contracts\Factory::class, \SocialiteProviders\Contracts\Factory::class];
    }
}
