<?php

namespace SocialiteProviders\Manager\Test\Stubs;

use SocialiteProviders\Manager\OAuth1\AbstractProvider;

class OAuth1ProviderStub extends AbstractProvider
{
    public const IDENTIFIER = 'TEST';

    protected function mapUserToObject(array $user): array
    {
        return [];
    }

    public static function additionalConfigKeys(): array
    {
        return [];
    }
}
