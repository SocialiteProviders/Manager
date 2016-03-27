<?php

namespace SocialiteProviders\Manager\Test;

use Mockery as m;
use SocialiteProviders\Manager\ServiceProvider;

class ServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function it_fires_an_event()
    {
        $app = m::mock(\Illuminate\Contracts\Foundation\Application::class);
        $socialiteWasCalled = m::mock(\SocialiteProviders\Manager\SocialiteWasCalled::class);

        $event = m::mock(\Illuminate\Contracts\Events\Dispatcher::class);
        $event->shouldReceive('fire')->with($socialiteWasCalled);

        $sp = new ServiceProvider($app);
        $sp->boot($event, $socialiteWasCalled);
    }
}
