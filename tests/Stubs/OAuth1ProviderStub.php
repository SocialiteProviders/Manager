<?php

namespace SocialiteProviders\Manager\Test\Stubs;

use SocialiteProviders\Manager\OAuth1\AbstractProvider;

class OAuth1ProviderStub extends AbstractProvider
{
    const IDENTIFIER = 'TEST';

    public static function additionalConfigKeys()
    {
        return [];
    }
}
