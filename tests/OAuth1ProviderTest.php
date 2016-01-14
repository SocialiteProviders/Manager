<?php

namespace SocialiteProviders\Manager;

use Mockery as m;

class OAuth1ProviderTest extends \PHPUnit_Framework_TestCase
{
    use ManagerTestTrait;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function it_should_build_a_provider_and_extend_socialite()
    {
        $providerName = 'bar';

        $socialite = $this->socialiteMock();
        $socialite->shouldReceive('formatConfig')->with($this->config())
            ->andReturn($this->oauth1FormattedConfig($this->config()));
        $socialite->shouldReceive('extend')->withArgs(
            [
                $providerName,
                m::on(
                    function ($closure) {
                        $this->assertInstanceOf($this->oauth1ProviderStubName(), $closure());

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
        $app->shouldReceive('make')->with('SocialiteProviders.config.' . $providerName)->andReturn($config);
        $app->shouldReceive('offsetGet')->with('request')->andReturn($this->buildRequest());
        $app->shouldReceive('offsetGet')->with('config')->andReturn($this->servicesArray($providerName));

        $s = new SocialiteWasCalled($app);
        $s->extendSocialite($providerName, $this->oauth1ProviderStubName(), $this->oauth1ServerStubName());
    }

    /**
     * @test
     */
    public function it_throws_if_given_an_invalid_oauth1_provider()
    {
        $this->expectManagerInvalidArgumentException();

        $providerName = 'foo';

        $socialite = $this->socialiteMock();
        $socialite->shouldReceive('formatConfig')->with($this->config())
            ->andReturn($this->oauth1FormattedConfig($this->config()));

        $app = $this->appMock();
        $app->shouldReceive('make')->andReturn($socialite);
        $app->shouldReceive('offsetGet')->andReturn($this->servicesArray($providerName));

        $s = new SocialiteWasCalled($app);
        $s->extendSocialite($providerName, $this->invalidClass(), $this->oauth1ServerStubName());
    }

    /**
     * @test
     */
    public function it_throws_if_given_an_invalid_oauth1_server()
    {
        $this->expectManagerInvalidArgumentException();

        $providerName = 'bar';

        $socialite = $this->socialiteMock();
        $socialite->shouldReceive('formatConfig')->with($this->config())
            ->andReturn($this->oauth1FormattedConfig($this->config()));

        $app = $this->appMock();
        $app->shouldReceive('make')->andReturn($socialite);
        $app->shouldReceive('offsetGet')->andReturn($this->servicesArray($providerName));

        $s = new SocialiteWasCalled($app);
        $s->extendSocialite($providerName, $this->oauth1ProviderStubName(), $this->invalidClass());
    }
}
