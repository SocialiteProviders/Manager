<?php

namespace SocialiteProviders\Manager;

use Illuminate\Contracts\Foundation\Application as LaravelApp;
use Laravel\Socialite\SocialiteManager;
use SocialiteProviders\Manager\Contracts;

class SocialiteWasCalled
{
    const SERVICE_CONTAINER_PREFIX = 'SocialiteProviders.config.';

    /**
     * @var LaravelApp
     */
    protected $app;

    /**
     * @param LaravelApp $app
     */
    public function __construct(LaravelApp $app)
    {
        $this->app = $app;
    }

    /**
     * @param string $providerName 'meetup'
     * @param string $providerClass 'Your\Name\Space\ClassNameProvider' must extend
     *                              either Laravel\Socialite\Two\AbstractProvider or
     *                              Laravel\Socialite\One\AbstractProvider
     * @param string $oauth1Server 'Your\Name\Space\ClassNameServer' must extend League\OAuth1\Client\Server\Server
     *
     * @throws InvalidArgumentException
     */
    public function extendSocialite($providerName, $providerClass, $oauth1Server = null)
    {
        /** @var SocialiteManager $socialite */
        $socialite = $this->app->make(\Laravel\Socialite\Contracts\Factory::class);
        $provider = $this->buildProvider($socialite, $providerName, $providerClass, $oauth1Server);
        $socialite->extend(
            $providerName,
            function () use ($provider) {
                return $provider;
            }
        );
    }

    /**
     * @param SocialiteManager $socialite
     * @param                  $providerName
     * @param string $providerClass
     * @param null|string $oauth1Server
     *
     * @return \Laravel\Socialite\One\AbstractProvider|\Laravel\Socialite\Two\AbstractProvider
     */
    protected function buildProvider(SocialiteManager $socialite, $providerName, $providerClass, $oauth1Server)
    {
        $config = $this->getConfig($providerName);
        if ($this->isOAuth1($oauth1Server)) {
            return $this->buildOAuth1Provider($providerClass, $oauth1Server, $socialite->formatConfig($config));
        }

        return $this->buildOAuth2Provider($socialite, $providerClass, $config);
    }

    /**
     * Build an OAuth 1 provider instance.
     *
     * @param string $providerClass must extend Laravel\Socialite\One\AbstractProvider
     * @param string $oauth1Server must extend League\OAuth1\Client\Server\Server
     * @param array $config
     *
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function buildOAuth1Provider($providerClass, $oauth1Server, array $config)
    {
        $this->classExtends($providerClass, \Laravel\Socialite\One\AbstractProvider::class);
        $this->classExtends($oauth1Server, \League\OAuth1\Client\Server\Server::class);

        return new $providerClass(
            $this->app->offsetGet('request'), new $oauth1Server($config)
        );
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param SocialiteManager $socialite
     * @param string $providerClass must extend Laravel\Socialite\Two\AbstractProvider
     * @param array $config
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function buildOAuth2Provider(SocialiteManager $socialite, $providerClass, array $config)
    {
        $this->classExtends($providerClass, \Laravel\Socialite\Two\AbstractProvider::class);

        return $socialite->buildProvider($providerClass, $config);
    }

    /**
     * @param string $providerName
     *
     * @return array
     */
    protected function getConfig($providerName)
    {
        try {
            /** @var Contracts\ConfigInterface $config */
            $config = $this->app->make(self::SERVICE_CONTAINER_PREFIX . $providerName);

            if (!($config instanceof Contracts\ConfigInterface)) {
                throw new InvalidArgumentException('Config class does not implement config contract');
            }

            return $config->get();
        } catch (\ReflectionException $e) {
            return $this->app->offsetGet('config')['services.' . $providerName] ?: (new Config('foobar', 'foobar', 'foobar'))->get();
        }
    }

    /**
     * Check if a server is given, which indicates that OAuth1 is used.
     *
     * @param string $oauth1Server
     *
     * @return bool
     */
    private function isOAuth1($oauth1Server)
    {
        return (!empty($oauth1Server));
    }

    /**
     * @param string $class
     * @param string $baseClass
     *
     * @throws InvalidArgumentException
     */
    private function classExtends($class, $baseClass)
    {
        if (false === is_subclass_of($class, $baseClass)) {
            $message = $class . ' does not extend ' . $baseClass;
            throw new InvalidArgumentException($message);
        }
    }
}
