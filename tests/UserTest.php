<?php

namespace SocialiteProviders\Manager\Test;

use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\OAuth2\User;

class UserTest extends TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     */
    public function we_should_be_able_to_set_the_credentials_body(): void
    {
        $credentialsBody = ['test'];
        $user = (new User())->setAccessTokenResponseBody($credentialsBody);

        $this->assertSame($user->accessTokenResponseBody, $credentialsBody);
    }
}
