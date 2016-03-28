<?php

namespace SocialiteProviders\Manager\Contracts\OAuth1;

use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;

interface ProviderInterface extends SocialiteOauth2ProviderInterface
{
    /**
     * @param Config $config
     *
     * @return $this
     */
    public function config(Config $config);
}
