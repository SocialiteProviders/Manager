<?php

namespace SocialiteProviders\Manager;

use Illuminate\Contracts\Container\Container as Application;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\One\AbstractProvider as SocialiteOAuth1AbstractProvider;
use Laravel\Socialite\SocialiteManager;
use Laravel\Socialite\Two\AbstractProvider as SocialiteOAuth2AbstractProvider;
use League\OAuth1\Client\Server\Server as OAuth1Server;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;
use SocialiteProviders\Manager\Exception\InvalidArgumentException;

class SocialiteWasCalled
{
    public const SERVICE_CONTAINER_PREFIX = 'SocialiteProviders.config.';

    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    private ConfigRetrieverInterface $configRetriever;

    /**
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @param  \SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface  $configRetriever
     */
    public function __construct(Application $app, ConfigRetrieverInterface $configRetriever)
    {
        $this->app = $app;
        $this->configRetriever = $configRetriever;
    }

    /**
     * @param  string  $providerName  'meetup'
     * @param  string  $providerClass  'Your\Name\Space\ClassNameProvider' must extend
     *                                 either Laravel\Socialite\Two\AbstractProvider or
     *                                 Laravel\Socialite\One\AbstractProvider
     * @param  string  $oauth1Server  'Your\Name\Space\ClassNameServer' must extend League\OAuth1\Client\Server\Server
     * @return void
     */
    public function extendSocialite($providerName, $providerClass, $oauth1Server = null)
    {
        /** @var SocialiteManager $socialite */
        $socialite = $this->app->make(SocialiteFactory::class);

        $this->classExists($providerClass);
        if ($this->isOAuth1($oauth1Server)) {
            $this->classExists($oauth1Server);
            $this->classExtends($providerClass, SocialiteOAuth1AbstractProvider::class);
        }

        $socialite->extend(
            $providerName,
            function () use ($socialite, $providerName, $providerClass, $oauth1Server) {
                $provider = $this->buildProvider($socialite, $providerName, $providerClass, $oauth1Server);
                if (defined('SOCIALITEPROVIDERS_STATELESS') && SOCIALITEPROVIDERS_STATELESS) {
                    return $provider->stateless();
                }

                return $provider;
            }
        );
    }

    /**
     * @param  \Laravel\Socialite\SocialiteManager  $socialite
     * @param  string  $providerName
     * @param  string  $providerClass
     * @param  null|string  $oauth1Server
     * @return \Laravel\Socialite\One\AbstractProvider|\Laravel\Socialite\Two\AbstractProvider
     */
    protected function buildProvider(SocialiteManager $socialite, $providerName, $providerClass, $oauth1Server)
    {
        if ($this->isOAuth1($oauth1Server)) {
            return $this->buildOAuth1Provider($socialite, $providerClass, $providerName, $oauth1Server);
        }

        return $this->buildOAuth2Provider($socialite, $providerClass, $providerName);
    }

    /**
     * Build an OAuth 1 provider instance.
     *
     * @param  \Laravel\Socialite\SocialiteManager  $socialite
     * @param  string  $providerClass  must extend Laravel\Socialite\One\AbstractProvider
     * @param  string  $providerName
     * @param  string  $oauth1Server  must extend League\OAuth1\Client\Server\Server
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function buildOAuth1Provider(SocialiteManager $socialite, $providerClass, $providerName, $oauth1Server)
    {
        $this->classExtends($oauth1Server, OAuth1Server::class);

        $config = $this->getConfig($providerClass, $providerName);

        $configServer = $socialite->formatConfig($config->get());

        $provider = new $providerClass(
            $this->app->offsetGet('request'), new $oauth1Server($configServer)
        );

        $provider->setConfig($config);

        return $provider;
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param  SocialiteManager  $socialite
     * @param  string  $providerClass  must extend Laravel\Socialite\Two\AbstractProvider
     * @param  string  $providerName
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function buildOAuth2Provider(SocialiteManager $socialite, $providerClass, $providerName)
    {
        $this->classExtends($providerClass, SocialiteOAuth2AbstractProvider::class);

        $config = $this->getConfig($providerClass, $providerName);

        $provider = $socialite->buildProvider($providerClass, $config->get());

        $provider->setConfig($config);

        return $provider;
    }

    /**
     * @param  string  $providerClass
     * @param  string  $providerName
     * @return \SocialiteProviders\Manager\Contracts\ConfigInterface
     */
    protected function getConfig(string $providerClass, string $providerName)
    {
        return $this->configRetriever->fromServices(
            $providerName, $providerClass::additionalConfigKeys()
        );
    }

    /**
     * Check if a server is given, which indicates that OAuth1 is used.
     *
     * @param  string  $oauth1Server
     * @return bool
     */
    private function isOAuth1($oauth1Server)
    {
        return ! empty($oauth1Server);
    }

    /**
     * @param  string  $class
     * @param  string  $baseClass
     * @return void
     *
     * @throws \SocialiteProviders\Manager\Exception\InvalidArgumentException
     */
    private function classExtends($class, $baseClass)
    {
        if (false === is_subclass_of($class, $baseClass)) {
            throw new InvalidArgumentException("{$class} does not extend {$baseClass}");
        }
    }

    /**
     * @param  string  $providerClass
     * @return void
     *
     * @throws \SocialiteProviders\Manager\Exception\InvalidArgumentException
     */
    private function classExists($providerClass)
    {
        if (! class_exists($providerClass)) {
            throw new InvalidArgumentException("{$providerClass} doesn't exist");
        }
    }
}
