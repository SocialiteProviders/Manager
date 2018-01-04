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
        $socialiteWasCalled = app(SocialiteWasCalled::class);

        event($socialiteWasCalled);
    }

    /**
     * Register the provider services.
     */
    public function register()
    {
        parent::register();

        if (class_exists('Laravel\Lumen\Application') && !defined('SOCIALITEPROVIDERS_STATELESS')) {
            define('SOCIALITEPROVIDERS_STATELESS', true);
        }

        $this->app->singleton(ConfigRetrieverInterface::class, function () {
            return new ConfigRetriever();
        });
    }
}
