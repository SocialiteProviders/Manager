<?php

namespace SocialiteProviders\Manager\Stubs;

use Laravel\Socialite\Two\AbstractProvider;

class OAuth2ProviderStub extends AbstractProvider
{
    protected $test = 'test';

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
