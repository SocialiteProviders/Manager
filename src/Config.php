<?php
namespace SocialiteProviders\Manager;

use SocialiteProviders\Manager\Contracts;

class Config implements Contracts\ConfigInterface
{

    protected $config;

    public function __construct($key, $secret, $callbackUri)
    {
        $this->config = [
            'client_id' => $key,
            'client_secret' => $secret,
            'redirect' => $callbackUri,
        ];
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->config;
    }
}
