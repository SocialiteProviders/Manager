<?php

namespace SocialiteProviders\Manager;

use Laravel\Socialite\SocialiteManager;
use Illuminate\Container\Container as Application;
use SocialiteProviders\Manager\Exception\MissingConfigException;
use SocialiteProviders\Manager\Exception\InvalidArgumentException;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;

class SocialiteWasCalled
{
    const SERVICE_CONTAINER_PREFIX = 'SocialiteProviders.config.';

    /**
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * @var \SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface
     */
    private $configRetriever;

    /**
     * @var bool
     */
    private $spoofedConfig = [
        'client_id' => 'spoofed_client_id',
        'client_secret' => 'spoofed_client_secret',
        'redirect' => 'spoofed_redirect',
    ];

    /**
     * @param \Illuminate\Container\Container $app
     * @param \SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface $configRetriever
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
     * @throws \InvalidArgumentException
     */
    public function extendSocialite($providerName, $providerClass, $oauth1Server = null)
    {
        /** @var SocialiteManager $socialite */
        $socialite = $this->app->make(\Laravel\Socialite\Contracts\Factory::class);

        $this->classExists($providerClass);
        if ($this->isOAuth1($oauth1Server)) {
            $this->classExists($oauth1Server);
            $this->classExtends($providerClass, \Laravel\Socialite\One\AbstractProvider::class);
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
     * @param \Laravel\Socialite\SocialiteManager $socialite
     * @param                  $providerName
     * @param string           $providerClass
     * @param null|string      $oauth1Server
     *
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
     * @param string $providerClass must extend Laravel\Socialite\One\AbstractProvider
     * @param string $oauth1Server  must extend League\OAuth1\Client\Server\Server
     * @param array  $config
     *
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function buildOAuth1Provider(SocialiteManager $socialite, $providerClass, $providerName, $oauth1Server)
    {
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
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     *
     * @return array
     */
    protected function getConfig($providerClass, $providerName)
    {
        $additionalConfigKeys = $providerClass::additionalConfigKeys();
        $exceptionMessages = [];

        // Environment Configuration
        $config = null;
        try {
            $config = $this->configRetriever->fromEnv($providerClass::IDENTIFIER, $additionalConfigKeys);

            return $config;
        } catch (MissingConfigException $e) {
            $exceptionMessages[] = $e->getMessage();
        }

        // Services Configuration
        $config = null;
        try {
            $config = $this->configRetriever->fromServices($providerName, $additionalConfigKeys);

            return $config;
        } catch (MissingConfigException $e) {
            $exceptionMessages[] = $e->getMessage();
        }

        // Spoofed Configuration
        return new Config(
            $this->spoofedConfig['client_id'],
            $this->spoofedConfig['client_secret'],
            $this->spoofedConfig['redirect']
        );

        // throw new MissingConfigException(implode(PHP_EOL, $exceptionMessages));
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
        return ! empty($oauth1Server);
    }

    /**
     * @param string $class
     * @param string $baseClass
     *
     * @throws \InvalidArgumentException
     */
    private function classExtends($class, $baseClass)
    {
        if (false === is_subclass_of($class, $baseClass)) {
            $message = $class.' does not extend '.$baseClass;
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @param  string $providerClass
     * @throws \InvalidArgumentException
     */
    private function classExists($providerClass)
    {
        if (! class_exists($providerClass)) {
            throw new InvalidArgumentException("$providerClass doesn't exist");
        }
    }
}
