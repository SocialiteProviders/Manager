<?php

namespace SocialiteProviders\Manager\Contracts\OAuth1;

use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;

interface ProviderInterface
{
    /**
     * @param \SocialiteProviders\Manager\Contracts\ConfigInterface $config
     *
     * @return $this
     */
    public function setConfig(Config $config);
}
