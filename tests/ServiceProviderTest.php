<?php

namespace SocialiteProviders\Manager;

use Mockery as m;

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
        $app = m::mock('Illuminate\Contracts\Foundation\Application');
        $socialiteWasCalled = m::mock('SocialiteProviders\Manager\SocialiteWasCalled');

        $event = m::mock('Illuminate\Contracts\Events\Dispatcher');
        $event->shouldReceive('fire')->with($socialiteWasCalled);

        $sp = new ServiceProvider($app);
        $sp->boot($event, $socialiteWasCalled);
    }
}
