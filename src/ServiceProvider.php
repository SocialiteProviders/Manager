<?php

namespace SocialiteProviders\Manager;

use Laravel\Socialite\SocialiteServiceProvider;
use SocialiteProviders\Manager\Helpers\ConfigRetriever;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;

class ServiceProvider extends SocialiteServiceProvider
{
    public function boot()
    {
        $socialiteWasCalled = app(SocialiteWasCalled::class);

        event($socialiteWasCalled);
    }

    public function register()
    {
        parent::register();

        if (class_exists('Laravel\Lumen\Application') && ! defined('SOCIALITEPROVIDERS_STATELESS')) {
            define('SOCIALITEPROVIDERS_STATELESS', true);
        }

        $this->app->singleton(ConfigRetrieverInterface::class, function () {
            return new ConfigRetriever();
        });
    }
}
