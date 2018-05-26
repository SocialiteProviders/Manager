<?php

namespace SocialiteProviders\Manager\Contracts\OAuth1;

use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;

interface ProviderInterface
{
    /**
     * @param Laravel\Socialite\SocialiteManager $socialite
     * @param \SocialiteProviders\Manager\Contracts\ConfigInterface $config
     *
     * @return $this
     */
    public function setConfig($socialite, Config $config);
}
