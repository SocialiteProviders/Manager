<?php

namespace SocialiteProviders\Manager\Contracts\Helpers;

interface ConfigRetrieverInterface
{
    /**
     * @param string $providerIdentifier
     * @param array  $additionalConfigKeys
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     *
     * @return \SocialiteProviders\Manager\Contracts\ConfigInterface
     */
    public function fromEnv($providerIdentifier, array $additionalConfigKeys = []);

    /**
     * @param string $providerName
     * @param array  $additionalConfigKeys
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     *
     * @return \SocialiteProviders\Manager\Contracts\ConfigInterface
     */
    public function fromServices($providerName, array $additionalConfigKeys = []);
}
