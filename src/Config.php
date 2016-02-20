<?php
namespace SocialiteProviders\Manager;

use SocialiteProviders\Manager\Contracts;

class Config implements Contracts\ConfigInterface
{

    protected $config;

    public function __construct($key, $secret, $callbackUri, array $additionalConfig = [])
    {
        $this->config = array_merge([
            'client_id' => $key,
            'client_secret' => $secret,
            'redirect' => $callbackUri,
        ], $additionalConfig);
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->config;
    }
}
