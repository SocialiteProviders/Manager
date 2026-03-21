<?php

namespace SocialiteProviders\Manager\Test;

use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\OAuth1\User;

class OAuth1UserTest extends TestCase
{
    /**
     * @test
     */
    public function we_should_be_able_to_set_the_credentials_body(): void
    {
        $credentialsBody = ['access_token' => 'tok', 'extra' => 'data'];
        $user = (new User)->setAccessTokenResponseBody($credentialsBody);

        $this->assertSame($credentialsBody, $user->accessTokenResponseBody);
    }

    /**
     * @test
     */
    public function setAccessTokenResponseBody_returns_self_for_fluent_chaining(): void
    {
        $user = new User;
        $result = $user->setAccessTokenResponseBody(['test']);

        $this->assertSame($user, $result);
    }
}
