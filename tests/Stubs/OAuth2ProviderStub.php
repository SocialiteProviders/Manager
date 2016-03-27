<?php

namespace SocialiteProviders\Manager\Test\Stubs;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class OAuth2ProviderStub extends AbstractProvider
{
    protected $test = 'test';
    const IDENTIFIER = 'TEST';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return 'test';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return $this->test;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        return [$this->test];
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return $this->test;
    }
}
