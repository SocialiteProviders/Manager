<?php

namespace SocialiteProviders\Manager\Contracts\OAuth2;

use Laravel\Socialite\Two\ProviderInterface as SocialiteOauth2ProviderInterface;
use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;

interface ProviderInterface extends SocialiteOauth2ProviderInterface
{
    /**
     * @param \SocialiteProviders\Manager\Contracts\ConfigInterface $config
     *
     * @return $this
     */
    public function setConfig(Config $config);
}
