<?php

namespace SocialiteProviders\Manager\OAuth1;

use GuzzleHttp\Exception\BadResponseException;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server as BaseServer;
use SocialiteProviders\Manager\ConfigTrait;

abstract class Server extends BaseServer
{
    use ConfigTrait;

    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ',';

    /**
     * Retrieves token credentials by passing in the temporary credentials,
     * the temporary credentials identifier as passed back by the server
     * and finally the verifier code.
     *
     * @param \League\OAuth1\Client\Credentials\TemporaryCredentials $temporaryCredentials
     * @param string                                                 $temporaryIdentifier
     * @param string                                                 $verifier
     *
     * @return \League\OAuth1\Client\Credentials\TokenCredentials
     */
    public function getTokenCredentials(TemporaryCredentials $temporaryCredentials, $temporaryIdentifier, $verifier)
    {
        if ($temporaryIdentifier !== $temporaryCredentials->getIdentifier()) {
            throw new \InvalidArgumentException(
                'Temporary identifier passed back by server does not match that of stored temporary credentials.
                Potential man-in-the-middle.'
            );
        }

        $uri = $this->urlTokenCredentials();
        $bodyParameters = ['oauth_verifier' => $verifier];

        $client = $this->createHttpClient();

        $headers = $this->getHeaders($temporaryCredentials, 'POST', $uri, $bodyParameters);

        try {
            if ('GuzzleHttp\\Client' === get_class($client)) {
                $response = $client->post($uri, [
                    'headers'     => $headers,
                    'form_params' => $bodyParameters,
                ]);
            } else {
                $response = $client->post($uri, $headers, $bodyParameters)->send();
            }
        } catch (BadResponseException $e) {
            return $this->handleTokenCredentialsBadResponse($e);
        }

        return [
            'tokenCredentials'        => $this->createTokenCredentials($response->getBody()),
            'credentialsResponseBody' => $response->getBody(),
        ];
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
        $this->scopes = array_unique(array_merge($this->scopes, $scopes));

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
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Format the given scopes.
     *
     * @param array  $scopes
     * @param string $scopeSeparator
     *
     * @return string
     */
    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }
}
