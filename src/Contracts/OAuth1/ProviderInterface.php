<?php

namespace SocialiteProviders\Manager\Contracts\OAuth1;

use Laravel\Socialite\One\ProviderInterface as SocialiteOauth1ProviderInterface;
use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;

interface ProviderInterface extends SocialiteOauth1ProviderInterface
{
    /**
     * @param Config $config
     *
     * @return $this
     */
    public function config(Config $config);
}
