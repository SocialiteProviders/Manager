<?php

namespace SocialiteProviders\Manager\Test\Stubs;

use Laravel\Socialite\One\AbstractProvider;

class OAuth1ProviderStub extends AbstractProvider
{
    const IDENTIFIER = 'TEST';

    public static function additionalConfigKeys()
    {
        return [];
    }
}
