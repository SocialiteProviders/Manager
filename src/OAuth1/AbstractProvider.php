<?php

namespace SocialiteProviders\Manager\OAuth1;

use SocialiteProviders\Manager\SocialiteWasCalled;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractProvider extends \Laravel\Socialite\One\AbstractProvider
{
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
            setcookie('oauth_temp', serialize($temp));
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
            $temp = unserialize($_COOKIE['oauth_temp']);

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
    public function stateless()
    {
        $this->stateless = true;

        return $this;
    }

    /**
     * Determine if the provider is operating as stateless.
     *
     * @return bool
     */
    protected function isStateless()
    {
        return $this->stateless;
    }

    public static function additionalConfigKeys()
    {
        return [];
    }
}
