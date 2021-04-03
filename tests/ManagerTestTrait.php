<?php

namespace SocialiteProviders\Manager\Test;

use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request as HttpRequest;
use Laravel\Socialite\SocialiteManager;
use Mockery as m;
use SocialiteProviders\Manager\Config;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;

trait ManagerTestTrait
{
    public static $functions;

    protected function setUp(): void
    {
        self::$functions = m::mock();
    }

    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @return \Mockery\MockInterface|\SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface
     */
    protected function configRetrieverMock()
    {
        return m::mock(ConfigRetrieverInterface::class);
    }

    /**
     * @return \Mockery\MockInterface|\Illuminate\Contracts\Container\Container
     */
    protected function appMock()
    {
        return m::mock(ContainerContract::class);
    }

    /**
     * @return \Mockery\MockInterface|\Illuminate\Contracts\Foundation\Application
     */
    protected function appMockWithBooted()
    {
        $app = m::mock(Application::class);
        $app->shouldReceive('booted')->with(m::on(function ($callback) {
            $callback();

            return true;
        }));

        return $app;
    }

    /**
     * @return \Mockery\MockInterface|\Laravel\Socialite\SocialiteManager
     */
    protected function socialiteMock()
    {
        return m::mock(SocialiteManager::class);
    }

    /**
     * @return \Mockery\MockInterface|\Illuminate\Http\Request
     */
    protected function buildRequest()
    {
        return m::mock(HttpRequest::class);
    }

    protected function configObject()
    {
        return new Config('test', 'test', 'test');
    }

    protected function configRetrieverMockWithDefaultExpectations($providerName, $providerClass)
    {
        $configRetriever = $this->configRetrieverMock();
        $configRetriever
            ->shouldReceive('fromServices')
            ->with($providerName, $providerClass::additionalConfigKeys())
            ->andReturn($this->configObject());

        return $configRetriever;
    }

    /**
     * @return array
     */
    protected function config()
    {
        return [
            'client_id' => 'test',
            'client_secret' => 'test',
            'redirect' => 'test',
        ];
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function oauth1FormattedConfig(array $config)
    {
        return [
            'identifier' => $config['client_id'],
            'secret' => $config['client_secret'],
            'callback_uri' => $config['redirect'],
        ];
    }

    protected function oauth2ProviderStub()
    {
        static $provider = null;

        if (is_null($provider)) {
            $provider = $this->mockStub('OAuth2ProviderStub')->shouldDeferMissing();
        }

        return $provider;
    }

    protected function oauth1ProviderStub()
    {
        static $provider = null;

        if (is_null($provider)) {
            $provider = $this->mockStub('OAuth1ProviderStub');
        }

        return $provider;
    }

    protected function oauth1ProviderStubClass()
    {
        return $this->fullStubClassName('OAuth1ProviderStub');
    }

    protected function oauth1ServerStubClass()
    {
        return $this->fullStubClassName('OAuth1ServerStub');
    }

    protected function oauth2ProviderStubClass()
    {
        return $this->fullStubClassName('OAuth2ProviderStub');
    }

    /**
     * @param string $stub
     *
     * @return \Mockery\MockInterface
     */
    protected function mockStub($stub)
    {
        return m::mock($this->fullStubClassName($stub));
    }

    /**
     * @param string $stub
     *
     * @return string
     */
    protected function fullStubClassName($stub)
    {
        return __NAMESPACE__.'\Stubs\\'.$stub;
    }

    /**
     * @return string
     */
    protected function invalidClass()
    {
        return 'FooBar';
    }
}
