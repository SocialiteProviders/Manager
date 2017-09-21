<?php

namespace SocialiteProviders\Manager\Contracts\OAuth2;

use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;
use Laravel\Socialite\Two\ProviderInterface as SocialiteOauth2ProviderInterface;

interface ProviderInterface extends SocialiteOauth2ProviderInterface
{
    /**
     * @param \SocialiteProviders\Manager\Contracts\ConfigInterface $config
     *
     * @return $this
     */
    public function setConfig(Config $config);
}
