<?php

namespace SocialiteProviders\Manager\Test\Stubs;

use Mockery as m;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;
use stdClass;

class OAuthTwoTestProviderStub extends AbstractProvider
{
    public const PROVIDER_NAME = 'test';

    public $http;

    public static function providerName(): string
    {
        return 'test';
    }

    protected function getAuthUrl($state)
    {
        return 'http://auth.url';
    }

    protected function getTokenUrl()
    {
        return 'http://token.url';
    }

    protected function getUserByToken($token)
    {
        return ['id' => 'foo'];
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->map(['id' => $user['id']]);
    }

    protected function getHttpClient()
    {
        if ($this->http) {
            return $this->http;
        }

        return $this->http = m::mock(stdClass::class);
    }
}
