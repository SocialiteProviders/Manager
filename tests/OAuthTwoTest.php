<?php

namespace SocialiteProviders\Manager\Test;

use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteOAuth2User;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Manager\Test\Stubs\OAuthTwoTestProviderStub;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OAuthTwoTest extends TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     */
    public function redirectGeneratesTheProperSymfonyRedirectResponse()
    {
        $session = m::mock(SessionContract::class);
        $request = Request::create('foo');
        $request->setLaravelSession($session);
        $session
            ->shouldReceive('put')
            ->once();
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('http://auth.url', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function it_can_return_the_service_container_key()
    {
        $result = OAuthTwoTestProviderStub::serviceContainerKey(OAuthTwoTestProviderStub::PROVIDER_NAME);

        $this->assertEquals('SocialiteProviders.config.test', $result);
    }

    /**
     * @test
     */
    public function userReturnsAUserInstanceForTheAuthenticatedRequest()
    {
        $session = m::mock(SessionInterface::class);
        $request = Request::create('foo', 'GET', [
            'state' => str_repeat('A', 40),
            'code' => 'code',
        ]);
        $request->setSession($session);
        $session
            ->shouldReceive('pull')
            ->once()
            ->with('state')
            ->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock(stdClass::class);
        $provider->http
            ->shouldReceive('post')
            ->once()
            ->with('http://token.url', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => 'client_id',
                    'client_secret' => 'client_secret',
                    'code' => 'code',
                    'redirect_uri' => 'redirect_uri',
                ],
            ])
            ->andReturn($response = m::mock(stdClass::class));
        $response
            ->shouldReceive('getBody')
            ->andReturn('{"access_token": "access_token", "test": "test"}');
        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('foo', $user->id);
    }

    /**
     * @test
     */
    public function access_token_response_body_is_accessible_from_user()
    {
        $session = m::mock(SessionInterface::class);
        $accessTokenResponseBody = '{"access_token": "access_token", "test": "test"}';
        $request = Request::create('foo', 'GET', [
            'state' => str_repeat('A', 40),
            'code' => 'code',
        ]);
        $request->setSession($session);
        $session
            ->shouldReceive('pull')
            ->once()
            ->with('state')
            ->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock(stdClass::class);
        $provider->http
            ->shouldReceive('post')
            ->once()
            ->with('http://token.url', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => 'client_id',
                    'client_secret' => 'client_secret',
                    'code' => 'code',
                    'redirect_uri' => 'redirect_uri',
                ],
            ])
            ->andReturn($response = m::mock(stdClass::class));
        $response
            ->shouldReceive('getBody')
            ->andReturn($accessTokenResponseBody);
        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('foo', $user->id);
        $this->assertEquals($user->accessTokenResponseBody, json_decode($accessTokenResponseBody, true));
    }

    /**
     * @test
     */
    public function regular_laravel_socialite_class_works_as_well()
    {
        $session = m::mock(SessionInterface::class);
        $accessTokenResponseBody = '{"access_token": "access_token", "test": "test"}';
        $request = Request::create('foo', 'GET', [
            'state' => str_repeat('A', 40),
            'code' => 'code',
        ]);
        $request->setSession($session);
        $session
            ->shouldReceive('pull')
            ->once()
            ->with('state')
            ->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');

        $provider->http = m::mock(stdClass::class);
        $provider->http
            ->shouldReceive('post')
            ->once()
            ->with('http://token.url', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => 'client_id',
                    'client_secret' => 'client_secret',
                    'code' => 'code',
                    'redirect_uri' => 'redirect_uri',
                ],
            ])
            ->andReturn($response = m::mock(stdClass::class));
        $response
            ->shouldReceive('getBody')
            ->andReturn($accessTokenResponseBody);
        $user = $provider->user();

        $this->assertInstanceOf(SocialiteOAuth2User::class, $user);
        $this->assertEquals('foo', $user->id);
    }

    /**
     * @test
     */
    public function exceptionIsThrownIfStateIsInvalid()
    {
        $this->expectException(InvalidStateException::class);

        $session = m::mock(SessionInterface::class);
        $request = Request::create('foo', 'GET', [
            'state' => str_repeat('B', 40),
            'code' => 'code',
        ]);
        $request->setSession($session);
        $session
            ->shouldReceive('pull')
            ->once()
            ->with('state')
            ->andReturn(str_repeat('A', 40));
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $provider->user();
    }

    /**
     * @test
     */
    public function exceptionIsThrownIfStateIsNotSet()
    {
        $this->expectException(InvalidStateException::class);

        $session = m::mock(SessionInterface::class);
        $request = Request::create('foo', 'GET', [
            'state' => 'state',
            'code' => 'code',
        ]);
        $request->setSession($session);
        $session
            ->shouldReceive('pull')
            ->once()
            ->with('state');
        $provider = new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $provider->user();
    }
}
