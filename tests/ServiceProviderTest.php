<?php

namespace SocialiteProviders\Manager\Test;

use Mockery as m;
use SocialiteProviders\Manager\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class ServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     */
    public function it_fires_an_event()
    {
        $socialiteWasCalledMock = m::mock(SocialiteWasCalled::class);
        self::$functions
            ->shouldReceive('app')
            ->with(SocialiteWasCalled::class)
            ->once()
            ->andReturn($socialiteWasCalledMock);

        self::$functions
            ->shouldReceive('event')
            ->with($socialiteWasCalledMock)
            ->once();

        $serviceProvider = new ServiceProvider($this->appMock());
        $serviceProvider->boot();

        $this->assertTrue(true);
    }
}

namespace SocialiteProviders\Manager;

use SocialiteProviders\Manager\Test\ServiceProviderTest;

function app($make)
{
    return ServiceProviderTest::$functions->app($make);
}

function event($event)
{
    return ServiceProviderTest::$functions->event($event);
}
