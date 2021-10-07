<?php

namespace SocialiteProviders\Manager\Contracts\Helpers;

interface ConfigRetrieverInterface
{
    /**
     * @param  string  $providerName
     * @param  array  $additionalConfigKeys
     * @return \SocialiteProviders\Manager\Contracts\ConfigInterface
     */
    public function fromServices($providerName, array $additionalConfigKeys = []);
}
