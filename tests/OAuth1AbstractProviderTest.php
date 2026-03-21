<?php

namespace SocialiteProviders\Manager\Test;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use League\OAuth1\Client\Credentials\CredentialsException;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\Config;
use SocialiteProviders\Manager\OAuth1\AbstractProvider as OAuth1AbstractProvider;
use SocialiteProviders\Manager\OAuth1\User;
use SocialiteProviders\Manager\Test\Stubs\OAuth1ServerStub;

class TestableOAuth1Provider extends OAuth1AbstractProvider
{
    public const IDENTIFIER = 'TEST';

    protected function mapUserToObject(array $user): User
    {
        $mapped = new User;
        $mapped->id = $user['id'] ?? null;
        $mapped->nickname = $user['nickname'] ?? null;

        return $mapped;
    }

    public static function additionalConfigKeys(): array
    {
        return [];
    }
}

class OAuth1AbstractProviderTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function makeServerStub(): OAuth1ServerStub
    {
        return new OAuth1ServerStub([
            'identifier'   => 'client_id',
            'secret'       => 'client_secret',
            'callback_uri' => 'http://localhost/callback',
        ]);
    }

    protected function makeProvider(?Request $request = null, ?OAuth1ServerStub $server = null): TestableOAuth1Provider
    {
        return new TestableOAuth1Provider(
            $request ?? Request::create('foo'),
            $server ?? $this->makeServerStub()
        );
    }

    protected function makeSessionRequest(array $params, TemporaryCredentials $tempCreds): Request
    {
        $request = Request::create('foo', 'GET', $params);
        $session = m::mock(\Illuminate\Contracts\Session\Session::class);
        $session->shouldReceive('get')
            ->with('oauth_temp')
            ->andReturn(serialize($tempCreds));
        $request->setLaravelSession($session);

        return $request;
    }

    /**
     * @test
     */
    public function serviceContainerKey_returns_prefixed_name(): void
    {
        $result = TestableOAuth1Provider::serviceContainerKey('twitter');

        $this->assertSame('SocialiteProviders.config.twitter', $result);
    }

    /**
     * @test
     */
    public function stateless_returns_self_for_fluent_chaining(): void
    {
        $provider = $this->makeProvider();

        $this->assertSame($provider, $provider->stateless());
    }

    /**
     * @test
     */
    public function stateless_sets_stateless_property(): void
    {
        $provider = $this->makeProvider();
        $provider->stateless(false);

        $reflection = new \ReflectionClass($provider);
        $prop = $reflection->getProperty('stateless');
        $prop->setAccessible(true);

        $this->assertFalse($prop->getValue($provider));
    }

    /**
     * @test
     */
    public function stateless_defaults_to_true_argument(): void
    {
        $provider = $this->makeProvider();
        $provider->stateless(false);
        $provider->stateless();

        $reflection = new \ReflectionClass($provider);
        $prop = $reflection->getProperty('stateless');
        $prop->setAccessible(true);

        $this->assertTrue($prop->getValue($provider));
    }

    /**
     * @test
     */
    public function scopes_delegates_to_server_and_returns_self(): void
    {
        $server = $this->makeServerStub();
        $provider = $this->makeProvider(null, $server);

        $result = $provider->scopes(['read', 'write']);

        $this->assertSame($provider, $result);
    }

    /**
     * @test
     */
    public function with_delegates_to_server_and_returns_self(): void
    {
        $server = $this->makeServerStub();
        $provider = $this->makeProvider(null, $server);

        $result = $provider->with(['foo' => 'bar']);

        $this->assertSame($provider, $result);
    }

    /**
     * @test
     */
    public function setConfig_delegates_to_server_and_returns_self(): void
    {
        $server = $this->makeServerStub();
        $provider = $this->makeProvider(null, $server);

        $config = new Config('id', 'secret', 'redirect');
        $result = @$provider->setConfig($config); // suppress dynamic property deprecation

        $this->assertSame($provider, $result);
    }

    /**
     * @test
     */
    public function user_throws_when_verifier_is_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing OAuth verifier');

        $request = Request::create('foo', 'GET', []);
        $provider = $this->makeProvider($request);

        $provider->user();
    }

    /**
     * @test
     */
    public function user_returns_user_with_token_and_access_token_response_body(): void
    {
        $server = m::mock(OAuth1ServerStub::class)->makePartial();

        $tempCreds = new TemporaryCredentials;
        $tempCreds->setIdentifier('temp_tok');
        $tempCreds->setSecret('temp_sec');

        $tokenCreds = new TokenCredentials;
        $tokenCreds->setIdentifier('access_tok');
        $tokenCreds->setSecret('access_sec');

        $server->shouldReceive('getTokenCredentials')
            ->once()
            ->andReturn([
                'tokenCredentials'        => $tokenCreds,
                'credentialsResponseBody' => 'oauth_token=access_tok&oauth_token_secret=access_sec&user_id=123',
            ]);

        $userDetails = new User;
        $userDetails->id = '123';
        $userDetails->nickname = 'test_user';
        $server->shouldReceive('getUserDetails')
            ->once()
            ->with($tokenCreds)
            ->andReturn($userDetails);

        $request = $this->makeSessionRequest(
            ['oauth_token' => 'temp_tok', 'oauth_verifier' => 'verifier_code'],
            $tempCreds
        );

        $provider = new TestableOAuth1Provider($request, $server);
        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('access_tok', $user->token);
        $this->assertSame('access_sec', $user->tokenSecret);
        $this->assertIsArray($user->accessTokenResponseBody);
        $this->assertSame('123', $user->accessTokenResponseBody['user_id']);
    }

    /**
     * @test
     */
    public function user_throws_credentials_exception_on_unparseable_response_body(): void
    {
        $this->expectException(CredentialsException::class);
        $this->expectExceptionMessage('Unable to parse token credentials response');

        $server = m::mock(OAuth1ServerStub::class)->makePartial();

        $tokenCreds = new TokenCredentials;
        $tokenCreds->setIdentifier('tok');
        $tokenCreds->setSecret('sec');

        $server->shouldReceive('getTokenCredentials')
            ->andReturn([
                'tokenCredentials'        => $tokenCreds,
                'credentialsResponseBody' => '',
            ]);

        $userDetails = new User;
        $server->shouldReceive('getUserDetails')->andReturn($userDetails);

        $tempCreds = new TemporaryCredentials;
        $tempCreds->setIdentifier('temp');
        $tempCreds->setSecret('sec');

        $request = $this->makeSessionRequest(
            ['oauth_token' => 'temp', 'oauth_verifier' => 'ver'],
            $tempCreds
        );

        $provider = new TestableOAuth1Provider($request, $server);
        $provider->user();
    }

    /**
     * @test
     */
    public function userFromTokenAndSecret_returns_user_with_credentials(): void
    {
        $server = m::mock(OAuth1ServerStub::class)->makePartial();

        $userDetails = new User;
        $userDetails->id = '456';
        $server->shouldReceive('getUserDetails')
            ->once()
            ->with(m::on(function ($creds) {
                return $creds instanceof TokenCredentials
                    && $creds->getIdentifier() === 'my_token'
                    && $creds->getSecret() === 'my_secret';
            }))
            ->andReturn($userDetails);

        $provider = new TestableOAuth1Provider(Request::create('foo'), $server);
        $user = $provider->userFromTokenAndSecret('my_token', 'my_secret');

        $this->assertSame('my_token', $user->token);
        $this->assertSame('my_secret', $user->tokenSecret);
    }

    /**
     * @test
     */
    public function redirect_stores_serialized_temp_and_returns_redirect(): void
    {
        $server = m::mock(OAuth1ServerStub::class)->makePartial();

        $tempCreds = new TemporaryCredentials;
        $tempCreds->setIdentifier('temp_id');
        $tempCreds->setSecret('temp_secret');

        $server->shouldReceive('getTemporaryCredentials')
            ->once()
            ->andReturn($tempCreds);
        $server->shouldReceive('getAuthorizationUrl')
            ->once()
            ->with($tempCreds)
            ->andReturn('http://auth.example.com?oauth_token=temp_id');

        $request = Request::create('foo');
        $session = m::mock(\Illuminate\Contracts\Session\Session::class);
        $session->shouldReceive('put')
            ->once()
            ->with('oauth_temp', serialize($tempCreds));
        $request->setLaravelSession($session);

        $provider = new TestableOAuth1Provider($request, $server);

        $response = $provider->redirect();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('http://auth.example.com?oauth_token=temp_id', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function getToken_deserializes_temp_credentials_from_session(): void
    {
        $server = m::mock(OAuth1ServerStub::class)->makePartial();

        $tempCreds = new TemporaryCredentials;
        $tempCreds->setIdentifier('temp_tok');
        $tempCreds->setSecret('temp_sec');

        $tokenCreds = new TokenCredentials;
        $tokenCreds->setIdentifier('tok');
        $tokenCreds->setSecret('sec');

        $server->shouldReceive('getTokenCredentials')
            ->once()
            ->with(
                m::on(fn ($c) => $c instanceof TemporaryCredentials && $c->getIdentifier() === 'temp_tok'),
                'temp_tok',
                'ver'
            )
            ->andReturn([
                'tokenCredentials'        => $tokenCreds,
                'credentialsResponseBody' => 'oauth_token=tok&oauth_token_secret=sec',
            ]);

        $userDetails = new User;
        $server->shouldReceive('getUserDetails')->andReturn($userDetails);

        $request = $this->makeSessionRequest(
            ['oauth_token' => 'temp_tok', 'oauth_verifier' => 'ver'],
            $tempCreds
        );

        $provider = new TestableOAuth1Provider($request, $server);

        $user = $provider->user();
        $this->assertSame('tok', $user->token);
    }
}
