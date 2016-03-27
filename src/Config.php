<?php

namespace SocialiteProviders\Manager;

class Config implements Contracts\ConfigInterface
{
    protected $config;

    /**
     * Config constructor.
     *
     * @param string $key
     * @param string $secret
     * @param string $callbackUri
     * @param array  $additionalProviderConfig
     */
    public function __construct($key, $secret, $callbackUri, array $additionalProviderConfig = [])
    {
        $this->config = array_merge([
            'client_id' => $key,
            'client_secret' => $secret,
            'redirect' => $callbackUri,
        ], $additionalProviderConfig);
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->config;
    }
}
