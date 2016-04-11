<?php

namespace SocialiteProviders\Manager;

use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;

trait ConfigTrait
{
    protected $config;

    public function setConfig(Config $config)
    {
        $config = $config->get();

        $this->config = $config;
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUrl = $config['redirect'];

        return $this;
    }

    protected function getConfig($key = null, $default = null)
    {
        return $key ? array_get($this->config, $key, $default) : $this->config;
    }

    public static function additionalConfigKeys()
    {
        return [];
    }
}
