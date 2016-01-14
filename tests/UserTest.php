<?php

namespace SocialiteProviders\Manager;

use Mockery as m;
use SocialiteProviders\Manager\OAuth2\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    use ManagerTestTrait;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function we_should_be_able_to_set_the_credentials_body()
    {
        $credentialsBody = ['test'];
        $user = (new User())->setAccessTokenResponseBody($credentialsBody);

        $this->assertSame($user->accessTokenResponseBody, $credentialsBody);
    }
}
