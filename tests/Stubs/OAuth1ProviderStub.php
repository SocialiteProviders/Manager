<?php


namespace SocialiteProviders\Manager\Test\Stubs;

use SocialiteProviders\Manager\OAuth1\AbstractProvider;

class OAuth1ProviderStub extends AbstractProvider
{
    const IDENTIFIER = 'TEST';

    protected function mapUserToObject(array $user)
    {
        return [];
    }

    public static function additionalConfigKeys()
    {
        return [];
    }
}
