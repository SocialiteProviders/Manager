<?php

namespace SocialiteProviders\Manager;

use Mockery as m;

trait ManagerTestTrait
{
    protected function expectManagerInvalidArgumentException()
    {
        $this->setExpectedException($this->fullClassName('InvalidArgumentException'));
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

    /**
     * @return \Mockery\MockInterface
     */
    protected function appMock()
    {
        return m::mock(\Illuminate\Contracts\Foundation\Application::class);
    }

    /**
     * @return \Mockery\MockInterface
     */
    protected function socialiteMock()
    {
        return m::mock(\Laravel\Socialite\SocialiteManager::class);
    }

    protected function oauth2ProviderStub()
    {
        static $provider = null;

        if (is_null($provider)) {
            $provider = $this->mockStub('OAuth2ProviderStub');
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

    protected function oauth1ProviderStubName()
    {
        return $this->fullStubClassName('OAuth1ProviderStub');
    }

    protected function oauth1ServerStubName()
    {
        return $this->fullStubClassName('OAuth1ServerStub');
    }

    protected function oauth2ProviderStubName()
    {
        return $this->fullStubClassName('OAuth2ProviderStub');
    }

    /**
     * @param string $stub
     *
     * @return m\MockInterface
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
        return __NAMESPACE__ . '\Stubs\\' . $stub;
    }

    /**
     * @param string $class
     *
     * @return string
     */
    protected function fullClassName($class)
    {
        return __NAMESPACE__ . '\\' . $class;
    }

    /**
     * @param string $providerName
     *
     * @return array
     */
    protected function servicesArray($providerName)
    {
        return [$this->providerConfigKey($providerName) => $this->config()];
    }

    /**
     * @param string $providerName
     *
     * @return string
     */
    protected function providerConfigKey($providerName)
    {
        return 'services.' . $providerName;
    }

    /**
     * @return m\MockInterface
     */
    protected function buildRequest()
    {
        return m::mock(\Illuminate\Http\Request::class);
    }

    /**
     * @return string
     */
    protected function invalidClass()
    {
        return 'FooBar';
    }
}
