<?php

namespace SocialiteProviders\Manager\Contracts\Helpers;

use SocialiteProviders\Manager\Contracts\ConfigInterface;
use SocialiteProviders\Manager\Exception\MissingConfigException;

interface ConfigRetrieverInterface
{
    /**
     * @param string $providerIdentifier
     * @param array  $additionalConfigKeys
     *
     * @throws MissingConfigException
     *
     * @return ConfigInterface
     */
    public function fromEnv($providerIdentifier, array $additionalConfigKeys = []);

    /**
     * @param string $providerName
     * @param array  $additionalConfigKeys
     *
     * @throws MissingConfigException
     *
     * @return ConfigInterface
     */
    public function fromServices($providerName, array $additionalConfigKeys = []);
}
