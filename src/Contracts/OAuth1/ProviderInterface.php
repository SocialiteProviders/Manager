<?php

namespace SocialiteProviders\Manager\Contracts\OAuth1;

use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;

interface ProviderInterface
{
    /**
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig(Config $config);
}
