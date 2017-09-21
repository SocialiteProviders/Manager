<?php

namespace SocialiteProviders\Manager;

use SocialiteProviders\Manager\Contracts\ConfigInterface;

trait ConfigTrait
{
    /**
     * @var \SocialiteProviders\Manager\Contracts\OAuth1\ProviderInterface|\SocialiteProviders\Manager\Contracts\OAuth2\ProviderInterface
     */
    protected $config;

    /**
     * @param \SocialiteProviders\Manager\Contracts\OAuth1\ProviderInterface|\SocialiteProviders\Manager\Contracts\OAuth2\ProviderInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $config = $config->get();

        $this->config = $config;
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUrl = $config['redirect'];

        return $this;
    }

    /**
     * @param  string $key
     * @param  mixed $default
     *
     * @return mixed|array
     */
    protected function getConfig($key = null, $default = null)
    {
        // check manually if a key is given and if it exists in the config
        // this has to be done to check for spoofed additional config keys so that null isn't returned
        if (! empty($key) && empty($this->config[$key])) {
            return $default;
        }

        return $key ? array_get($this->config, $key, $default) : $this->config;
    }

    /**
     * @return array
     */
    public static function additionalConfigKeys()
    {
        return [];
    }
}
