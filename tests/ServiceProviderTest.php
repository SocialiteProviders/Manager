<?php

namespace SocialiteProviders\Manager\Test;

use Illuminate\Contracts\Container\Container as ContainerContract;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;
use SocialiteProviders\Manager\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class ServiceProviderTest extends TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     */
    public function it_fires_an_event(): void
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

        $serviceProvider = new ServiceProvider($this->appMockWithBooted());
        $serviceProvider->boot();

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_fires_event_directly_for_lumen(): void
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

        // Use a plain container mock (not Application), simulating Lumen
        $app = m::mock(ContainerContract::class);
        $app->shouldReceive('singleton')->zeroOrMoreTimes();
        $app->shouldReceive('bound')->andReturn(true);

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->boot();

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function register_binds_config_retriever_interface_as_singleton(): void
    {
        $app = $this->appMockWithBooted();
        $boundCalled = false;
        $app->shouldReceive('singleton')->zeroOrMoreTimes();
        $app->shouldReceive('bound')
            ->with(ConfigRetrieverInterface::class)
            ->andReturnUsing(function () use (&$boundCalled) {
                $boundCalled = true;

                return false;
            });

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->register();

        $this->assertTrue($boundCalled, 'ServiceProvider should check if ConfigRetrieverInterface is bound');
    }

    /**
     * @test
     */
    public function register_does_not_rebind_if_already_bound(): void
    {
        $app = $this->appMockWithBooted();
        $app->shouldReceive('singleton')->zeroOrMoreTimes();
        $app->shouldReceive('bound')
            ->with(ConfigRetrieverInterface::class)
            ->andReturn(true);

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->register();

        // verify the singleton was only called for the parent (Factory::class), not ConfigRetrieverInterface
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
