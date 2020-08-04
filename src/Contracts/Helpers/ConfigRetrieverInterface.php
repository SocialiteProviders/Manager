<?php
declare(strict_types=1);

namespace SocialiteProviders\Manager\Contracts\Helpers;

use SocialiteProviders\Manager\Contracts\ConfigInterface;
use SocialiteProviders\Manager\Exception\MissingConfigException;

interface ConfigRetrieverInterface
{
    /**
     * @param string $providerName
     * @param array  $additionalConfigKeys
     *
     * @return ConfigInterface
     *
     * @throws MissingConfigException
     */
    public function fromServices($providerName, array $additionalConfigKeys = []): ConfigInterface;
}
