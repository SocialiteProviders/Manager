<?php

namespace SocialiteProviders\Manager;

use Laravel\Socialite\SocialiteServiceProvider;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;
use SocialiteProviders\Manager\Helpers\ConfigRetriever;

class ServiceProvider extends SocialiteServiceProvider
{
    /**
     * Bootstrap the provider services.
     */
    public function boot()
    {
        $this->app->booted(function () {
            event(app(SocialiteWasCalled::class));
        });
    }

    /**
     * Register the provider services.
     */
    public function register()
    {
        parent::register();

        if (! $this->app->bound(ConfigRetrieverInterface::class)) {
            $this->app->singleton(ConfigRetrieverInterface::class, fn () => new ConfigRetriever);
        }
    }
}
