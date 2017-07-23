<?php

namespace SocialiteProviders\Manager\Contracts\OAuth2;

use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;
use Laravel\Socialite\Two\ProviderInterface as SocialiteOauth2ProviderInterface;

interface ProviderInterface extends SocialiteOauth2ProviderInterface
{
    /**
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig(Config $config);
}
