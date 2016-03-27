<?php

namespace SocialiteProviders\Manager;

use Mockery as m;
use SocialiteProviders\Manager\Stubs\OAuth2ProviderStub;

class OAuth2ProviderTest extends \PHPUnit_Framework_TestCase
{
    use ManagerTestTrait;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function it_passes()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_allows_a_custom_config_to_be_passed_dynamically()
    {
        $provider = new OAuth2ProviderStub(\Mockery::mock(\Illuminate\Http\Request::class), 'client id', 'client secret', 'redirect url');

        $result = $provider->config(new Config('key', 'secret', 'callback uri'));

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
        $app->shouldReceive('offsetGet')->with('config')->andReturn($this->servicesArray($providerName));

        $s = new SocialiteWasCalled($app);
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
        $app->shouldReceive('offsetGet')->andReturn($this->servicesArray($providerName));

        $s = new SocialiteWasCalled($app);
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
        $app->shouldReceive('offsetGet')->andReturn($this->servicesArray($providerName));

        $s = new SocialiteWasCalled($app);
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
        $app->shouldReceive('offsetGet')->andReturn($this->servicesArray($providerName));

        $s = new SocialiteWasCalled($app);
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
        $app->shouldReceive('offsetGet')->andReturn($this->servicesArray($providerName));

        $s = new SocialiteWasCalled($app);
        $s->extendSocialite($providerName, $this->oauth2ProviderStubName(), $this->oauth1ServerStubName());
    }
}
