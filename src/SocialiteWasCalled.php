<?php
namespace SocialiteProviders\Manager;

use Illuminate\Foundation\Application as LaravelApp;
use Laravel\Socialite\SocialiteManager;

class SocialiteWasCalled
{
    /**
     * The OAuth1 config-key for the client ID.
     *
     * @var string
     */
    protected $clientIdConfigKey = 'identifier';

    /**
     * The OAuth1 config-key for the client secret.
     *
     * @var string
     */
    protected $clientSecretConfigKey = 'secret';

    /**
     * The OAuth1 config-key for the callback url.
     *
     * @var string
     */
    protected $redirectConfigKey = 'callback_uri';

    /**
     * @param string $providerName 'meetup'
     * @param string $providerClass 'Your\Name\Space\ClassNameProvider' must extend
     *      either Laravel\Socialite\Two\AbstractProvider or Laravel\Socialite\One\AbstractProvider
     * @param string $providerServer 'Your\Name\Space\ClassNameServer' must extend League\OAuth1\Client\Server\Server
     */
    public function extendSocialite($providerName, $providerClass, $providerServer = null)
    {
        /** @var SocialiteManager $socialite */
        $socialite = app()->make('Laravel\Socialite\Contracts\Factory');

        $socialite->extend(
            $providerName,
            function ($app) use ($socialite, $providerName, $providerClass, $providerServer) {
                /** @var LaravelApp $app */
                $config = $app['config']['services.' . $providerName];

                if ($this->isOAuth1Provider($providerServer)) {
                    return $this->buildOAuth1Provider($app, $providerClass, $providerServer, $config);
                }

                return $this->buildOAuth2Provider($socialite, $providerClass, $config);
            }
        );
    }

    /**
     * @param string $clientIdKey key for 'client_id' replaces 'identifier'
     * @param string $clientSecretKey key for 'client_secret' replaces 'secret'
     * @param string $redirectKey key for 'redirect' replaces 'callback_uri'
     */
    public function overrideOAuth1ConfigFormat($clientIdKey, $clientSecretKey, $redirectKey)
    {
        $this->clientIdConfigKey = $clientIdKey;
        $this->clientSecretConfigKey = $clientSecretKey;
        $this->redirectConfigKey = $redirectKey;
    }

    /**
     * Build an OAuth 1 provider instance.
     *
     * @param  LaravelApp $app
     * @param  string $providerClass must extend Laravel\Socialite\One\AbstractProvider
     * @param  string $providerServer must extend League\OAuth1\Client\Server\Server
     * @param  array $config
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function buildOAuth1Provider(LaravelApp $app, $providerClass, $providerServer, array $config)
    {
        return new $providerClass($app['request'], new $providerServer($this->buildOAuth1Config($config)));
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param  SocialiteManager $socialite
     * @param  string $providerClass must extend Laravel\Socialite\Two\AbstractProvider
     * @param  array $config
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function buildOAuth2Provider(SocialiteManager $socialite, $providerClass, array $config)
    {
        return $socialite->buildProvider($providerClass, $config);
    }

    /**
     * Format the OAuth1 server configuration.
     *
     * @param  array $config
     * @return array
     */
    protected function buildOAuth1Config(array $config)
    {
        return [
            $this->clientIdConfigKey => $config['client_id'],
            $this->clientSecretConfigKey => $config['client_secret'],
            $this->redirectConfigKey => $config['redirect'],
        ];
    }

    /**
     * Check if a server is given, which indicates that OAuth1 is used.
     *
     * @param  string $providerServer
     * @return boolean
     */
    private function isOAuth1Provider($providerServer)
    {
        return (!empty($providerServer));
    }
}
