<?php

namespace SocialiteProviders\Manager\Test;

use Laravel\Socialite\Contracts\Factory as SocialiteFactoryContract;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\Config;
use SocialiteProviders\Manager\Exception\InvalidArgumentException;
use SocialiteProviders\Manager\Exception\MissingConfigException;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Manager\Test\Stubs\OAuth2ProviderStub;

class OAuth2ProviderTest extends TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     */
    public function it_throws_if_there_is_no_config_in_services_or_env(): void
    {
        $this->expectException(MissingConfigException::class);

        $providerName = 'bar';
        $providerClass = $this->oauth2ProviderStubClass();
        $socialite = $this->socialiteMock();
        $socialite
            ->shouldReceive('buildProvider')
            ->withArgs([$providerClass, $this->config()])
            ->andReturn($this->oauth2ProviderStub());
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
        $configRetriever = $this->configRetrieverMock();
        $configRetriever
            ->shouldReceive('fromServices')
            ->andThrow(MissingConfigException::class);
        $event = new SocialiteWasCalled($app, $configRetriever);

        $event->extendSocialite($providerName, $providerClass);
    }

    /**
     * @test
     */
    public function it_allows_the_config_to_be_retrieved_from_the_services_array(): void
    {
        $providerName = 'bar';
        $providerClass = $this->oauth2ProviderStubClass();
        $socialite = $this->socialiteMock();
        $socialite
            ->shouldReceive('buildProvider')
            ->withArgs([$providerClass, $this->config()])
            ->andReturn($this->oauth2ProviderStub());
        $socialite
            ->shouldReceive('extend')
            ->withArgs([
                $providerName,
                m::on(function ($closure) use ($providerClass) {
                    $this->assertInstanceOf($providerClass, $closure());

                    return is_callable($closure);
                }),
            ]);
        $config = $this->configObject();
        $app = $this->appMock();
        $app
            ->shouldReceive('make')
            ->with(SocialiteFactoryContract::class)
            ->andReturn($socialite);
        $configRetriever = $this->configRetrieverMock();
        $configRetriever
            ->shouldReceive('fromServices')
            ->andReturn($config);
        $event = new SocialiteWasCalled($app, $configRetriever);

        $event->extendSocialite($providerName, $providerClass);
    }

    /**
     * @test
     */
    public function it_allows_a_custom_config_to_be_passed_dynamically(): void
    {
        $provider = new OAuth2ProviderStub(
            $this->buildRequest(),
            'client id',
            'client secret',
            'redirect url'
        );

        $result = $provider->setConfig(new Config('key', 'secret', 'callback uri'));

        $this->assertSame($provider, $result);
    }

    /**
     * @test
     */
    public function it_retrieves_from_the_config_if_no_config_is_provided(): void
    {
        $providerName = 'bar';
        $providerClass = $this->oauth2ProviderStubClass();
        $socialite = $this->socialiteMock();
        $socialite
            ->shouldReceive('buildProvider')
            ->withArgs([$providerClass, $this->config()])
            ->andReturn($this->oauth2ProviderStub());
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
        $configRetriever = $this->configRetrieverMockWithDefaultExpectations(
            $providerName,
            $providerClass
        );
        $event = new SocialiteWasCalled($app, $configRetriever);

        $event->extendSocialite($providerName, $providerClass);
    }

    /**
     * @test
     */
    public function it_should_build_a_provider_and_extend_socialite(): void
    {
        $providerName = 'bar';
        $providerClass = $this->oauth2ProviderStubClass();
        $socialite = $this->socialiteMock();
        $socialite
            ->shouldReceive('buildProvider')
            ->withArgs([$providerClass, $this->config()])
            ->andReturn($this->oauth2ProviderStub());
        $socialite
            ->shouldReceive('extend')
            ->withArgs([
                $providerName,
                m::on(function ($closure) use ($providerClass) {
                    $this->assertInstanceOf($providerClass, $closure());

                    return is_callable($closure);
                }),
            ]);
        $config = $this->configObject();
        $app = $this->appMock();
        $app
            ->shouldReceive('make')
            ->with(SocialiteFactoryContract::class)
            ->andReturn($socialite);
        $app
            ->shouldReceive('make')
            ->with("SocialiteProviders.config.{$providerName}")
            ->andReturn($config);
        $configRetriever = $this->configRetrieverMockWithDefaultExpectations(
            $providerName,
            $providerClass
        );
        $event = new SocialiteWasCalled($app, $configRetriever);

        $event->extendSocialite($providerName, $providerClass);
    }

    /**
     * @test
     */
    public function it_throws_if_given_a_bad_provider_class_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $providerName = 'bar';
        $providerClass = $this->oauth2ProviderStubClass();
        $socialite = $this->socialiteMock();
        $socialite
            ->shouldReceive('buildProvider')
            ->withArgs([$providerClass, $this->config()])
            ->andReturn($this->oauth2ProviderStub());
        $socialite
            ->shouldReceive('extend')
            ->withArgs([
                $providerName,
                m::on(function ($closure) use ($providerClass) {
                    $this->assertInstanceOf($providerClass, $closure());

                    return is_callable($closure);
                }),
            ]);
        $config = $this->configObject();
        $app = $this->appMock();
        $app
            ->shouldReceive('make')
            ->with(SocialiteFactoryContract::class)
            ->andReturn($socialite);
        $app
            ->shouldReceive('make')
            ->with("SocialiteProviders.config.{$providerName}")
            ->andReturn($config);
        $configRetriever = $this->configRetrieverMockWithDefaultExpectations(
            $providerName,
            $providerClass
        );
        $event = new SocialiteWasCalled($app, $configRetriever);

        $event->extendSocialite($providerName, $this->invalidClass());
    }

    /**
     * @test
     */
    public function it_throws_if_given_an_invalid_oauth2_provider(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $providerName = 'foo';
        $providerClass = $this->oauth2ProviderStubClass();
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

        $event->extendSocialite($providerName, $this->invalidClass());
    }

    /**
     * @test
     */
    public function it_throws_if_oauth1_server_is_passed_for_oauth2(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $providerName = 'baz';
        $providerClass = $this->oauth2ProviderStubClass();
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

        $event->extendSocialite($providerName, $providerClass, $this->oauth1ServerStubClass());
    }
}
