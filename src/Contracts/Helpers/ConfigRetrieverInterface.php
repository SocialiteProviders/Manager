<?php

namespace SocialiteProviders\Manager\Contracts\Helpers;

interface ConfigRetrieverInterface
{
    /**
     * @param string $providerName
     * @param array  $additionalConfigKeys
     *
     * @return \SocialiteProviders\Manager\Contracts\ConfigInterface
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     */
    public function fromServices($providerName, array $additionalConfigKeys = []);
}
