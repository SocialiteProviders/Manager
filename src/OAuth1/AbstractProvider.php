<?php

namespace SocialiteProviders\Manager\OAuth1;

use Laravel\Socialite\One\AbstractProvider as BaseProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Manager\ConfigTrait;
use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractProvider extends BaseProvider
{
    use ConfigTrait;

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $stateless = true;

    /**
     * @var array
     */
    protected $credentialsResponseBody;

    public static function serviceContainerKey($providerName)
    {
        return SocialiteWasCalled::SERVICE_CONTAINER_PREFIX.$providerName;
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if (!$this->hasNecessaryVerifier()) {
            throw new \InvalidArgumentException('Invalid request. Missing OAuth verifier.');
        }

        $token = $this->getToken();
        $tokenCredentials = $token['tokenCredentials'];

        $user = $this->mapUserToObject((array) $this->server->getUserDetails($tokenCredentials));

        $user->setToken($tokenCredentials->getIdentifier(), $tokenCredentials->getSecret());

        if ($user instanceof User) {
            parse_str($token['credentialsResponseBody'], $credentialsResponseBody);

            if (!$credentialsResponseBody || !is_array($credentialsResponseBody)) {
                throw new CredentialsException('Unable to parse token credentials response.');
            }

            $user->setAccessTokenResponseBody($credentialsResponseBody);
        }

        return $user;
    }

    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return RedirectResponse
     */
    public function redirect()
    {
        if (!$this->isStateless()) {
            $this->request->getSession()->set(
                'oauth.temp', $temp = $this->server->getTemporaryCredentials()
            );
        } else {
            $temp = $this->server->getTemporaryCredentials();
            $this->request->session()->set('oauth_temp', serialize($temp));
        }

        return new RedirectResponse($this->server->getAuthorizationUrl($temp));
    }

    /**
     * Get the token credentials for the request.
     *
     * @return \League\OAuth1\Client\Credentials\TokenCredentials
     */
    protected function getToken()
    {
        if (!$this->isStateless()) {
            $temp = $this->request->getSession()->get('oauth.temp');

            return $this->server->getTokenCredentials(
                $temp, $this->request->get('oauth_token'), $this->request->get('oauth_verifier')
            );
        } else {
            $temp = unserialize($this->request->session()->get('oauth_temp'));

            return $this->server->getTokenCredentials(
                $temp, $this->request->get('oauth_token'), $this->request->get('oauth_verifier')
            );
        }
    }

    /**
     * Indicates that the provider should operate as stateless.
     *
     * @return $this
     */
    public function stateless($stateless = true)
    {
        $this->stateless = $stateless;

        return $this;
    }

    /**
     * Determine if the provider is operating as stateless.
     *
     * @return bool
     */
    protected function isStateless()
    {
        if (defined('SOCIALITEPROVIDERS_STATELESS')) {
            return true;
        }

        return $this->stateless;
    }

    /**
     * Set the scopes of the requested access.
     *
     * @param array $scopes
     *
     * @return $this
     */
    public function scopes(array $scopes)
    {
        $this->server = $this->server->scopes($scopes);

        return $this;
    }

    /**
     * Set the custom parameters of the request.
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function with(array $parameters)
    {
        $this->server = $this->server->with($parameters);

        return $this;
    }

    /**
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $this->server->setConfig($config);

        return $this;
    }
}
