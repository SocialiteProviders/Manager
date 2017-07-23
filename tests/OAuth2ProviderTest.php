<?php

namespace SocialiteProviders\Manager\Test;

use Mockery as m;
use SocialiteProviders\Manager\Config;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Manager\Test\Stubs\OAuth2ProviderStub;
use SocialiteProviders\Manager\Exception\MissingConfigException;

class OAuth2ProviderTest extends \PHPUnit_Framework_TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     */
    public function it_passes()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @expectedException \SocialiteProviders\Manager\Exception\MissingConfigException
     */
    public function it_throws_if_there_is_no_config_in_services_or_env()
    {
        $providerName = 'bar';

        $socialite = $this->socialiteMock();
        $socialite->shouldReceive('buildProvider')->withArgs([$this->oauth2ProviderStubName(), $this->config()])
            ->andReturn($this->oauth2ProviderStub());
        $socialite->shouldReceive('extend')->withArgs(
            [
                $providerName,
                m::on(
                    function ($closure) {
                        $this->assertInstanceOf($this->oauth2ProviderStubName(), $closure());

                        return is_callable($closure);
                    }
                ),
            ]
        );

        $app = $this->appMock();
        $app->shouldReceive('make')->with(\Laravel\Socialite\Contracts\Factory::class)->andReturn($socialite);

        $providerClass = $this->oauth2ProviderStubName();

        $configRetriever = $this->configRetrieverMock();
        $configRetriever->shouldReceive('fromEnv')->andThrow(MissingConfigException::class);
        $configRetriever->shouldReceive('fromServices')->andThrow(MissingConfigException::class);

        $s = new SocialiteWasCalled($app, $configRetriever);
        $s->extendSocialite($providerName, $this->oauth2ProviderStubName());
    }

    /**
     * @test
     */
    public function it_allows_the_config_to_be_retrieved_from_the_services_array()
    {
        $providerName = 'bar';

        $socialite = $this->socialiteMock();
        $socialite->shouldReceive('buildProvider')->withArgs([$this->oauth2ProviderStubName(), $this->config()])
            ->andReturn($this->oauth2ProviderStub());
        $socialite->shouldReceive('extend')->withArgs(
            [
                $providerName,
                m::on(
                    function ($closure) {
                        $this->assertInstanceOf($this->oauth2ProviderStubName(), $closure());

                        return is_callable($closure);
                    }
                ),
            ]
        );

        $app = $this->appMock();
        $app->shouldReceive('make')->with(\Laravel\Socialite\Contracts\Factory::class)->andReturn($socialite);

        $providerClass = $this->oauth2ProviderStubName();

        $configRetriever = $this->configRetrieverMock();
        $configRetriever->shouldReceive('fromEnv')->andThrow(MissingConfigException::class);
        $configRetriever->shouldReceive('fromServices')->with($providerName, $providerClass::additionalConfigKeys())->andReturn($this->configObject());

        $s = new SocialiteWasCalled($app, $configRetriever);
        $s->extendSocialite($providerName, $this->oauth2ProviderStubName());
    }

    /**
     * @test
     */
    public function it_allows_a_custom_config_to_be_passed_dynamically()
    {
        $provider = new OAuth2ProviderStub(\Mockery::mock(\Illuminate\Http\Request::class), 'client id', 'client secret', 'redirect url');

        $result = $provider->setConfig(new Config('key', 'secret', 'callback uri'));

        $this->assertEquals($provider, $result);
    }

    /**
     * @test
     */
    public function it_retrieves_from_the_config_if_no_config_is_provided()
    {
        $providerName = 'bar';

        $socialite = $this->socialiteMock();
        $socialite->shouldReceive('buildProvider')->withArgs([$this->oauth2ProviderStubName(), $this->config()])
            ->andReturn($this->oauth2ProviderStub());
        $socialite->shouldReceive('extend')->withArgs(
            [
                $providerName,
                m::on(
                    function ($closure) {
                        $this->assertInstanceOf($this->oauth2ProviderStubName(), $closure());

                        return is_callable($closure);
                    }
                ),
            ]
        );

        $app = $this->appMock();
        $app->shouldReceive('make')->with(\Laravel\Socialite\Contracts\Factory::class)->andReturn($socialite);

        $s = new SocialiteWasCalled($app, $this->configRetrieverMockWithDefaultExpectations($this->oauth2ProviderStubName()));
        $s->extendSocialite($providerName, $this->oauth2ProviderStubName());
    }

    /**
     * @test
     */
    public function it_should_build_a_provider_and_extend_socialite()
    {
        $providerName = 'bar';

        $socialite = $this->socialiteMock();
        $socialite->shouldReceive('buildProvider')->withArgs([$this->oauth2ProviderStubName(), $this->config()])
            ->andReturn($this->oauth2ProviderStub());
        $socialite->shouldReceive('extend')->withArgs(
            [
                $providerName,
                m::on(
                    function ($closure) {
                        $this->assertInstanceOf($this->oauth2ProviderStubName(), $closure());

                        return is_callable($closure);
                    }
                ),
            ]
        );

        $config = new Config(
            $this->config()['client_id'],
            $this->config()['client_secret'],
            $this->config()['redirect']
        );

        $app = $this->appMock();
        $app->shouldReceive('make')->with(\Laravel\Socialite\Contracts\Factory::class)->andReturn($socialite);
        $app->shouldReceive('make')->with('SocialiteProviders.config.'.$providerName)->andReturn($config);

        $s = new SocialiteWasCalled($app, $this->configRetrieverMockWithDefaultExpectations($this->oauth2ProviderStubName()));
        $s->extendSocialite($providerName, $this->oauth2ProviderStubName());
    }

    /**
     * @test
     * @expectedException \SocialiteProviders\Manager\Exception\InvalidArgumentException
     */
    public function it_throws_if_given_a_bad_provider_class_name()
    {
        $providerName = 'bar';

        $socialite = $this->socialiteMock();
        $socialite->shouldReceive('buildProvider')->withArgs([$this->oauth2ProviderStubName(), $this->config()])
            ->andReturn($this->oauth2ProviderStub());
        $socialite->shouldReceive('extend')->withArgs(
            [
                $providerName,
                m::on(
                    function ($closure) {
                        $this->assertInstanceOf($this->oauth2ProviderStubName(), $closure());

                        return is_callable($closure);
                    }
                ),
            ]
        );

        $config = new Config(
            $this->config()['client_id'],
            $this->config()['client_secret'],
            $this->config()['redirect']
        );

        $app = $this->appMock();
        $app->shouldReceive('make')->with(\Laravel\Socialite\Contracts\Factory::class)->andReturn($socialite);
        $app->shouldReceive('make')->with('SocialiteProviders.config.'.$providerName)->andReturn($config);

        $s = new SocialiteWasCalled($app, $this->configRetrieverMockWithDefaultExpectations($this->oauth2ProviderStubName()));
        $s->extendSocialite($providerName, 'foobar');
    }

    /**
     * @test
     */
    public function it_throws_if_given_an_invalid_oauth2_provider()
    {
        $this->expectManagerInvalidArgumentException();

        $providerName = 'foo';

        $socialite = $this->socialiteMock();

        $app = $this->appMock();
        $app->shouldReceive('make')->andReturn($socialite);

        $s = new SocialiteWasCalled($app, $this->configRetrieverMockWithDefaultExpectations($this->oauth2ProviderStubName()));
        $s->extendSocialite($providerName, $this->invalidClass());
    }

    /**
     * @test
     */
    public function it_throws_if_oauth1_server_is_passed_for_oauth2()
    {
        $this->expectManagerInvalidArgumentException();

        $providerName = 'baz';

        $socialite = $this->socialiteMock();
        $socialite->shouldReceive('formatConfig')->with($this->config())
            ->andReturn($this->oauth1FormattedConfig($this->config()));

        $app = $this->appMock();
        $app->shouldReceive('make')->andReturn($socialite);

        $s = new SocialiteWasCalled($app, $this->configRetrieverMockWithDefaultExpectations($this->oauth2ProviderStubName()));
        $s->extendSocialite($providerName, $this->oauth2ProviderStubName(), $this->oauth1ServerStubName());
    }
}
