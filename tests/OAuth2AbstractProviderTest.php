<?php

namespace SocialiteProviders\Manager\Test;

use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Http\Request;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Manager\Test\Stubs\OAuthTwoTestProviderStub;
use stdClass;

class OAuth2AbstractProviderTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function makeProvider(): OAuthTwoTestProviderStub
    {
        $session = m::mock(SessionContract::class);
        $request = Request::create('foo', 'GET', [
            'state' => str_repeat('A', 40),
            'code'  => 'code',
        ]);
        $request->setLaravelSession($session);
        $session
            ->shouldReceive('pull')
            ->once()
            ->with('state')
            ->andReturn(str_repeat('A', 40));

        return new OAuthTwoTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
    }

    protected function mockHttpResponse(OAuthTwoTestProviderStub $provider, string $body): void
    {
        $provider->http = m::mock(stdClass::class);
        $provider->http
            ->shouldReceive('post')
            ->once()
            ->andReturn($response = m::mock(stdClass::class));
        $response
            ->shouldReceive('getBody')
            ->andReturn($body);
    }

    /**
     * @test
     */
    public function parseApprovedScopes_returns_empty_array_when_scope_is_null(): void
    {
        $provider = $this->makeProvider();
        $this->mockHttpResponse($provider, '{"access_token": "tok"}');

        $user = $provider->user();

        $this->assertSame([], $user->approvedScopes);
    }

    /**
     * @test
     */
    public function parseApprovedScopes_returns_array_when_scope_is_array(): void
    {
        $provider = $this->makeProvider();
        $this->mockHttpResponse($provider, '{"access_token": "tok", "scope": ["read", "write"]}');

        $user = $provider->user();

        $this->assertSame(['read', 'write'], $user->approvedScopes);
    }

    /**
     * @test
     */
    public function parseApprovedScopes_splits_string_scope_by_separator(): void
    {
        $provider = $this->makeProvider();
        $this->mockHttpResponse($provider, '{"access_token": "tok", "scope": "read,write,admin"}');

        $user = $provider->user();

        $this->assertSame(['read', 'write', 'admin'], $user->approvedScopes);
    }

    /**
     * @test
     */
    public function parseApprovedScopes_returns_empty_array_for_numeric_scope(): void
    {
        $provider = $this->makeProvider();
        $this->mockHttpResponse($provider, '{"access_token": "tok", "scope": 42}');

        $user = $provider->user();

        $this->assertSame([], $user->approvedScopes);
    }

    /**
     * @test
     */
    public function parseApprovedScopes_returns_empty_array_for_boolean_scope(): void
    {
        $provider = $this->makeProvider();
        $this->mockHttpResponse($provider, '{"access_token": "tok", "scope": true}');

        $user = $provider->user();

        $this->assertSame([], $user->approvedScopes);
    }

    /**
     * @test
     */
    public function user_sets_refresh_token_and_expires_in(): void
    {
        $provider = $this->makeProvider();
        $this->mockHttpResponse($provider, '{"access_token": "tok", "refresh_token": "refresh", "expires_in": 3600}');

        $user = $provider->user();

        $this->assertSame('refresh', $user->refreshToken);
        $this->assertSame(3600, $user->expiresIn);
    }
}
