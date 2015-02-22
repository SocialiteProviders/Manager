<?php
namespace SocialiteProviders\Manager;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Socialite\SocialiteServiceProvider;

class ServiceProvider extends SocialiteServiceProvider
{
    /**
     * @param Dispatcher $event
     */
    public function boot(Dispatcher $event)
    {
        $event->fire(new SocialiteWasCalled($this->app));
    }
}
