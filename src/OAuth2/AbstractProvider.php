<?php

namespace SocialiteProviders\Manager\OAuth2;

use GuzzleHttp\ClientInterface;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteProviders\Manager\Contracts\OAuth2\ProviderInterface;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;

abstract class AbstractProvider extends \Laravel\Socialite\Two\AbstractProvider implements ProviderInterface
{
    /**
     * @var array
     */
    protected $credentialsResponseBody;

    public static function serviceContainerKey($providerName)
    {
        return SocialiteWasCalled::SERVICE_CONTAINER_PREFIX.$providerName;
    }

    public static function additionalConfigKeys()
    {
        return [];
    }

    /**
     * @param Config $config
     *
     * @return $this
     */
    public function config(Config $config)
    {
        $config = $config->get();
        $this->clientId = $config['client_id'];
        $this->redirectUrl = $config['redirect'];
        $this->clientSecret = $config['client_secret'];

        return $this;
    }

    /**
     * @return \SocialiteProviders\Manager\OAuth2\User
     */
    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException();
        }

        $user = $this->mapUserToObject($this->getUserByToken(
            $token = $this->getAccessToken($this->getCode())
        ));

        $user->setToken($token);

        if ($user instanceof User) {
            return $user->setAccessTokenResponseBody($this->credentialsResponseBody);
        }

        return $user;
    }

    /**
     * Get the access token for the given code.
     *
     * @param string $code
     *
     * @return string
     */
    public function getAccessToken($code)
    {
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            $postKey => $this->getTokenFields($code),
        ]);

        $this->credentialsResponseBody = json_decode($response->getBody(), true);

        return $this->parseAccessToken($response->getBody());
    }
}
