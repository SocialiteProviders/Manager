<?php

namespace SocialiteProviders\Manager\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Socialite\SocialiteServiceProvider;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;
use SocialiteProviders\Manager\Helpers\ConfigRetriever;
use SocialiteProviders\Manager\SocialiteWasCalled;

class LaravelServiceProvider extends SocialiteServiceProvider
{
    /**
     * @param Dispatcher         $event
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function boot(Dispatcher $event, SocialiteWasCalled $socialiteWasCalled)
    {
        $event->fire($socialiteWasCalled);
    }

    public function register()
    {
        parent::register();

        $this->app->singleton(ConfigRetrieverInterface::class, function () {
            return new ConfigRetriever();
        });
    }
}
