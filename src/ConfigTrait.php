<?php
declare(strict_types=1);

namespace SocialiteProviders\Manager;

use Illuminate\Support\Arr;
use SocialiteProviders\Manager\Contracts\ConfigInterface;

trait ConfigTrait
{
    /**
     * @var array
     */
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

    /**
     * @return array
     */
    public static function additionalConfigKeys(): array
    {
        return [];
    }

    /**
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed|array
     */
    protected function getConfig(?string $key = null, $default = null)
    {
        // check manually if a key is given and if it exists in the config
        // this has to be done to check for spoofed additional config keys so that null isn't returned
        if (!empty($key) && empty($this->config[$key])) {
            return $default;
        }

        return $key ? Arr::get($this->config, $key, $default) : $this->config;
    }
}
