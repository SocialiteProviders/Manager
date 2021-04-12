<?php

namespace SocialiteProviders\Manager\Test;

use Laravel\Socialite\Contracts\Factory as SocialiteFactoryContract;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\Exception\InvalidArgumentException;
use SocialiteProviders\Manager\SocialiteWasCalled;

class OAuth1ProviderTest extends TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     */
    public function it_should_build_a_provider_and_extend_socialite()
    {
        $providerName = 'bar';
        $providerClass = $this->oauth1ProviderStubClass();
        $socialite = $this->socialiteMock();
        $socialite
            ->shouldReceive('formatConfig')
            ->with($this->config())
            ->andReturn($this->oauth1FormattedConfig($this->config()));
        $socialite
            ->shouldReceive('extend')
            ->withArgs([
                $providerName,
                m::on(function ($closure) use ($providerClass) {
                    $this->assertInstanceOf($providerClass, $closure());

                    return is_callable($closure);
                }),
            ]);
        $app = $this->appMock();
        $app
            ->shouldReceive('make')
            ->with(SocialiteFactoryContract::class)
            ->andReturn($socialite);
        $app
            ->shouldReceive('offsetGet')
            ->with('request')
            ->andReturn($this->buildRequest());
        $configRetriever = $this->configRetrieverMockWithDefaultExpectations(
            $providerName,
            $providerClass
        );
        $event = new SocialiteWasCalled($app, $configRetriever);

        $event->extendSocialite(
            $providerName,
            $providerClass,
            $this->oauth1ServerStubClass()
        );
    }

    /**
     * @test
     */
    public function it_throws_if_given_an_invalid_oauth1_provider()
    {
        $this->expectExceptionObject(new InvalidArgumentException("FooBar doesn't exist"));

        $providerName = 'foo';
        $providerClass = $this->oauth1ProviderStubClass();
        $socialite = $this->socialiteMock();
        $socialite
            ->shouldReceive('formatConfig')
            ->with($this->config())
            ->andReturn($this->oauth1FormattedConfig($this->config()));
        $app = $this->appMock();
        $app
            ->shouldReceive('make')
            ->andReturn($socialite);
        $configRetriever = $this->configRetrieverMockWithDefaultExpectations(
            $providerName,
            $providerClass
        );
        $event = new SocialiteWasCalled($app, $configRetriever);

        $event->extendSocialite(
            $providerName,
            $this->invalidClass(),
            $this->oauth1ServerStubClass()
        );
    }

    /**
     * @test
     */
    public function it_throws_if_given_an_invalid_oauth1_server()
    {
        $this->expectExceptionObject(new InvalidArgumentException("FooBar doesn't exist"));

        $providerName = 'bar';
        $providerClass = $this->oauth1ProviderStubClass();
        $socialite = $this->socialiteMock();
        $app = $this->appMock();
        $app
            ->shouldReceive('make')
            ->andReturn($socialite);
        $configRetriever = $this->configRetrieverMockWithDefaultExpectations(
            $providerName,
            $providerClass
        );
        $event = new SocialiteWasCalled($app, $configRetriever);

        $event->extendSocialite(
            $providerName,
            $providerClass,
            $this->invalidClass()
        );
    }
}
