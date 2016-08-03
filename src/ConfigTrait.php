<?php

namespace SocialiteProviders\Manager;

use SocialiteProviders\Manager\Contracts\ConfigInterface;

trait ConfigTrait
{
    protected $config;

    public function setConfig(ConfigInterface $config)
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
        // check manually if a key is given and if it exists in the config
        // this has to be done to check for spoofed additional config keys so that null isn't returned
        if (!empty($key) && empty($this->config[$key])) {
            return $default;
        }

        return $key ? array_get($this->config, $key, $default) : $this->config;
    }

    public static function additionalConfigKeys()
    {
        return [];
    }
}
