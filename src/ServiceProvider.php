<?php

namespace SocialiteProviders\Manager;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Socialite\SocialiteServiceProvider;

class ServiceProvider extends SocialiteServiceProvider
{
    /**
     * @param Dispatcher $event
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function boot(Dispatcher $event, SocialiteWasCalled $socialiteWasCalled)
    {
        $event->fire($socialiteWasCalled);
    }
}
