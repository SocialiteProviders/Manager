<?php

namespace SocialiteProviders\Manager\OAuth2;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider as BaseProvider;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteProviders\Manager\ConfigTrait;
use SocialiteProviders\Manager\Contracts\OAuth2\ProviderInterface;
use SocialiteProviders\Manager\SocialiteWasCalled;

abstract class AbstractProvider extends BaseProvider implements ProviderInterface
{
    use ConfigTrait;

    /**
     * @var array
     */
    protected $credentialsResponseBody;

    /**
     * The cached user instance.
     *
     * @var \SocialiteProviders\Manager\OAuth2\User|null
     */
    protected $user;

    /**
     * @param  string  $providerName
     * @return string
     */
    public static function serviceContainerKey($providerName)
    {
        return SocialiteWasCalled::SERVICE_CONTAINER_PREFIX.$providerName;
    }

    /**
     * @return \SocialiteProviders\Manager\OAuth2\User
     *
     * @throws \Laravel\Socialite\Two\InvalidStateException
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getAccessTokenResponse($this->getCode());
        $this->credentialsResponseBody = $response;

        $this->user = $this->mapUserToObject($this->getUserByToken(
            $token = $this->parseAccessToken($response)
        ));

        if ($this->user instanceof User) {
            $this->user->setAccessTokenResponseBody($this->credentialsResponseBody);
        }

        return $this->user->setToken($token)
            ->setRefreshToken($this->parseRefreshToken($response))
            ->setExpiresIn($this->parseExpiresIn($response))
            ->setApprovedScopes($this->parseApprovedScopes($response));
    }

    /**
     * Get the access token from the token response body.
     *
     * @param  array  $body
     * @return string
     */
    protected function parseAccessToken($body)
    {
        return Arr::get($body, 'access_token');
    }

    /**
     * Get the refresh token from the token response body.
     *
     * @param  array  $body
     * @return string
     */
    protected function parseRefreshToken($body)
    {
        return Arr::get($body, 'refresh_token');
    }

    /**
     * Get the expires in from the token response body.
     *
     * @param  array  $body
     * @return string
     */
    protected function parseExpiresIn($body)
    {
        return Arr::get($body, 'expires_in');
    }

    /**
     * Get the approved scopes from the token response body.
     *
     * @param  array  $body
     * @return array
     */
    protected function parseApprovedScopes($body)
    {
        $scopesRaw = Arr::get($body, 'scope', null);

        if (! is_array($scopesRaw) && ! is_string($scopesRaw)) {
            return [];
        }

        if (is_array($scopesRaw)) {
            return $scopesRaw;
        }

        return explode($this->scopeSeparator, (string) Arr::get($body, 'scope', ''));
    }
}
