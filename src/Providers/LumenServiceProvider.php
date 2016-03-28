<?php

namespace SocialiteProviders\Manager\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Socialite\SocialiteServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;
use SocialiteProviders\Manager\Helpers\ConfigRetriever;

class LumenServiceProvider extends SocialiteServiceProvider
{
    /**
     * @param Dispatcher         $event
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function boot()
    {
        $socialiteWasCalled = app(SocialiteWasCalled::class);

        event($socialiteWasCalled);
    }

    public function register()
    {
        parent::register();

        $this->app->singleton(ConfigRetrieverInterface::class, function () {
            return new ConfigRetriever();
        });
    }
}
