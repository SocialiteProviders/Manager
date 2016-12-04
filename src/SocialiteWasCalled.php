<?php

namespace SocialiteProviders\Manager;

use Illuminate\Container\Container as Application;
use Laravel\Socialite\SocialiteManager;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;
use SocialiteProviders\Manager\Exception\InvalidArgumentException;
use SocialiteProviders\Manager\Exception\MissingConfigException;

class SocialiteWasCalled
{
    const SERVICE_CONTAINER_PREFIX = 'SocialiteProviders.config.';

    /**
     * @var LaravelApp
     */
    protected $app;

    /**
     * @var ConfigRetrieverInterface
     */
    private $configRetriever;

    /**
     * @var bool
     */
    public static $spoofedConfig = false;

    /**
     * @param LaravelApp               $app
     * @param ConfigRetrieverInterface $configRetriever
     */
    public function __construct(Application $app, ConfigRetrieverInterface $configRetriever)
    {
        $this->app = $app;
        $this->configRetriever = $configRetriever;
    }

    /**
     * @param string $providerName  'meetup'
     * @param string $providerClass 'Your\Name\Space\ClassNameProvider' must extend
     *                              either Laravel\Socialite\Two\AbstractProvider or
     *                              Laravel\Socialite\One\AbstractProvider
     * @param string $oauth1Server  'Your\Name\Space\ClassNameServer' must extend League\OAuth1\Client\Server\Server
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
                if (defined('SOCIALITEPROVIDERS_STATELESS') && SOCIALITEPROVIDERS_STATELESS) {
                    return $provider->stateless();
                }

                return $provider;
            }
        );
    }

    /**
     * @param SocialiteManager $socialite
     * @param                  $providerName
     * @param string           $providerClass
     * @param null|string      $oauth1Server
     *
     * @return \Laravel\Socialite\One\AbstractProvider|\Laravel\Socialite\Two\AbstractProvider
     */
    protected function buildProvider(SocialiteManager $socialite, $providerName, $providerClass, $oauth1Server)
    {
        $this->classExists($providerClass);

        if ($this->isOAuth1($oauth1Server)) {
            $this->classExists($oauth1Server);

            return $this->buildOAuth1Provider($socialite, $providerClass, $providerName, $oauth1Server);
        }

        return $this->buildOAuth2Provider($socialite, $providerClass, $providerName);
    }

    /**
     * Build an OAuth 1 provider instance.
     *
     * @param string $providerClass must extend Laravel\Socialite\One\AbstractProvider
     * @param string $oauth1Server  must extend League\OAuth1\Client\Server\Server
     * @param array  $config
     *
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function buildOAuth1Provider(SocialiteManager $socialite, $providerClass, $providerName, $oauth1Server)
    {
        $this->classExtends($providerClass, \Laravel\Socialite\One\AbstractProvider::class);
        $this->classExtends($oauth1Server, \League\OAuth1\Client\Server\Server::class);

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
     * @param SocialiteManager $socialite
     * @param string           $providerClass must extend Laravel\Socialite\Two\AbstractProvider
     * @param array            $config
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function buildOAuth2Provider(SocialiteManager $socialite, $providerClass, $providerName)
    {
        $this->classExtends($providerClass, \Laravel\Socialite\Two\AbstractProvider::class);

        $config = $this->getConfig($providerClass, $providerName);

        $provider = $socialite->buildProvider($providerClass, $config->get());

        $provider->setConfig($config);

        return $provider;
    }

    /**
     * @param string $providerClass
     * @param string $providerName
     *
     * @throws MissingConfigException
     *
     * @return array
     */
    protected function getConfig($providerClass, $providerName)
    {
        $config = null;
        $additionalConfigKeys = $providerClass::additionalConfigKeys();
        $exceptionMessages = [];
        try {
            $config = $this->configRetriever->fromEnv($providerClass::IDENTIFIER, $additionalConfigKeys);

            // We will use the $spoofedConfig variable for now as a way to find out if there was no
            // configuration in the .env file which means we should not return anything and jump
            // to the service config check to check if something can be found there.
            if (!static::$spoofedConfig) {
                return $config;
            }
        } catch (MissingConfigException $e) {
            $exceptionMessages[] = $e->getMessage();
        }

        $config = null;
        try {
            $config = $this->configRetriever->fromServices($providerName, $additionalConfigKeys);

            // Here we don't need to check for the $spoofedConfig variable because this will be
            // the end of checking for config and should contain either the proper data or an
            // array containing spoofed values.
            return $config;
        } catch (MissingConfigException $e) {
            $exceptionMessages[] = $e->getMessage();
        }

        throw new MissingConfigException(implode(PHP_EOL, $exceptionMessages));
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
        return !empty($oauth1Server);
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
            $message = $class.' does not extend '.$baseClass;
            throw new InvalidArgumentException($message);
        }
    }

    private function classExists($providerClass)
    {
        if (!class_exists($providerClass)) {
            throw new InvalidArgumentException("$providerClass doesn't exist");
        }
    }
}
