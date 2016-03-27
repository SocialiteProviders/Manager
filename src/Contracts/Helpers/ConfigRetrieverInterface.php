<?php
namespace SocialiteProviders\Manager\Contracts\Helpers;

use SocialiteProviders\Manager\Contracts\ConfigInterface;

interface ConfigRetrieverInterface
{
    /**
     * @param string $providerName
     * @param array  $additionalConfigKeys
     *
     * @return ConfigInterface
     */
    public function fromEnv($providerName, array $additionalConfigKeys = []);

    /**
     * @param string $providerName
     * @param array  $additionalConfigKeys
     *
     * @return ConfigInterface
     */
    public function fromServices($providerName, array $additionalConfigKeys = []);
}
