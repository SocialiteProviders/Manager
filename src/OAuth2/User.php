<?php
namespace SocialiteProviders\Manager\OAuth2;

class User extends \Laravel\Socialite\Two\User
{
    /**
     * The User Credentials
     *
     * e.g. access_token, refresh_token, etc.
     *
     * @var array
     */
    public $accessTokenResponseBody;

    /**
     * Set the credentials on the user.
     *
     * Might include things such as the token and refresh token
     *
     * @param array $accessTokenResponseBody
     *
     * @return $this
     */
    public function setAccessTokenResponseBody(array $accessTokenResponseBody)
    {
        $this->accessTokenResponseBody = $accessTokenResponseBody;

        return $this;
    }
}
