<?php
namespace SocialiteProviders\Manager\Contracts\OAuth2;

use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;

interface Provider extends ProviderInterface
{
    /**
     * @param Config $config
     *
     * @return $this
     */
    public function config(Config $config);
}
