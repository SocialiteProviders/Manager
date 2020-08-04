<?php
declare(strict_types=1);

namespace SocialiteProviders\Manager\Contracts\OAuth1;

use SocialiteProviders\Manager\Contracts\ConfigInterface as Config;

interface ProviderInterface
{
    public function setConfig(Config $config);
}
