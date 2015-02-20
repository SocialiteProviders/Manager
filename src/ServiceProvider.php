<?php
namespace SocialiteProviders\Manager;

use Laravel\Socialite\SocialiteServiceProvider;

class ServiceProvider extends SocialiteServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        event(new SocialiteWasCalled());
    }
}
