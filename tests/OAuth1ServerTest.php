<?php

namespace SocialiteProviders\Manager\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Response;
use League\OAuth1\Client\Credentials\CredentialsException;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use SocialiteProviders\Manager\Test\Stubs\OAuth1ServerStub;

class OAuth1ServerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function makeServer(): OAuth1ServerStub
    {
        return new OAuth1ServerStub([
            'identifier'   => 'client_id_test',
            'secret'       => 'client_secret_test',
            'callback_uri' => 'http://localhost/callback',
        ]);
    }

    protected function makeTemporaryCredentials(string $identifier = 'temp_id', string $secret = 'temp_secret'): TemporaryCredentials
    {
        $credentials = new TemporaryCredentials();
        $credentials->setIdentifier($identifier);
        $credentials->setSecret($secret);

        return $credentials;
    }

    protected function makeSuccessResponse(string $body = 'oauth_token=tok123&oauth_token_secret=secret456'): Response
    {
        return new Response(200, [], $body);
    }

    /**
     * @test
     */
    public function getTokenCredentials_returns_array_with_credentials_and_body(): void
    {
        $server = $this->makeServer();
        $server->http = m::mock(Client::class);
        $server->http
            ->shouldReceive('post')
            ->once()
            ->with('test', m::on(function ($options) {
                return isset($options['headers'])
                    && isset($options['form_params']['oauth_verifier'])
                    && $options['form_params']['oauth_verifier'] === 'test_verifier';
            }))
            ->andReturn($this->makeSuccessResponse());

        $tempCreds = $this->makeTemporaryCredentials();
        $result = $server->getTokenCredentials($tempCreds, 'temp_id', 'test_verifier');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tokenCredentials', $result);
        $this->assertArrayHasKey('credentialsResponseBody', $result);
        $this->assertInstanceOf(TokenCredentials::class, $result['tokenCredentials']);
    }

    /**
     * @test
     */
    public function getTokenCredentials_returns_parsed_token_values(): void
    {
        $server = $this->makeServer();
        $server->http = m::mock(Client::class);
        $server->http
            ->shouldReceive('post')
            ->andReturn($this->makeSuccessResponse('oauth_token=my_token&oauth_token_secret=my_secret'));

        $tempCreds = $this->makeTemporaryCredentials();
        $result = $server->getTokenCredentials($tempCreds, 'temp_id', 'verifier');

        $this->assertSame('my_token', $result['tokenCredentials']->getIdentifier());
        $this->assertSame('my_secret', $result['tokenCredentials']->getSecret());
    }

    /**
     * @test
     */
    public function getTokenCredentials_returns_raw_response_body(): void
    {
        $body = 'oauth_token=tok&oauth_token_secret=sec&extra_field=bonus';
        $server = $this->makeServer();
        $server->http = m::mock(Client::class);
        $server->http
            ->shouldReceive('post')
            ->andReturn($this->makeSuccessResponse($body));

        $tempCreds = $this->makeTemporaryCredentials();
        $result = $server->getTokenCredentials($tempCreds, 'temp_id', 'verifier');

        $this->assertSame($body, (string) $result['credentialsResponseBody']);
    }

    /**
     * @test
     */
    public function getTokenCredentials_preserves_extra_fields_in_body(): void
    {
        $body = 'oauth_token=tok&oauth_token_secret=sec&user_id=42&screen_name=test';
        $server = $this->makeServer();
        $server->http = m::mock(Client::class);
        $server->http
            ->shouldReceive('post')
            ->andReturn($this->makeSuccessResponse($body));

        $tempCreds = $this->makeTemporaryCredentials();
        $result = $server->getTokenCredentials($tempCreds, 'temp_id', 'verifier');

        parse_str((string) $result['credentialsResponseBody'], $parsed);
        $this->assertSame('42', $parsed['user_id']);
        $this->assertSame('test', $parsed['screen_name']);
    }

    /**
     * @test
     */
    public function getTokenCredentials_throws_on_mismatched_identifier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Temporary identifier passed back by server does not match');

        $server = $this->makeServer();
        $server->http = m::mock(Client::class);
        $server->http->shouldNotReceive('post');

        $tempCreds = $this->makeTemporaryCredentials('real_id');
        $server->getTokenCredentials($tempCreds, 'DIFFERENT_id', 'verifier');
    }

    /**
     * @test
     */
    public function getTokenCredentials_does_not_make_http_call_on_mismatched_identifier(): void
    {
        $server = $this->makeServer();
        $server->http = m::mock(Client::class);
        $server->http->shouldNotReceive('post');

        $tempCreds = $this->makeTemporaryCredentials('id_a');

        try {
            $server->getTokenCredentials($tempCreds, 'id_b', 'verifier');
        } catch (\InvalidArgumentException $e) {
            // Expected — Mockery will verify shouldNotReceive in tearDown
        }
    }

    /**
     * @test
     */
    public function getTokenCredentials_throws_credentials_exception_on_bad_response(): void
    {
        $this->expectException(CredentialsException::class);
        $this->expectExceptionMessage('401');

        $server = $this->makeServer();

        $badResponse = new Response(401, [], 'Unauthorized');
        $request = m::mock(RequestInterface::class);
        $exception = new BadResponseException('Bad response', $request, $badResponse);

        $server->http = m::mock(Client::class);
        $server->http
            ->shouldReceive('post')
            ->once()
            ->andThrow($exception);

        $tempCreds = $this->makeTemporaryCredentials();
        $server->getTokenCredentials($tempCreds, 'temp_id', 'verifier');
    }

    /**
     * @test
     */
    public function getTokenCredentials_includes_status_code_in_credentials_exception(): void
    {
        $server = $this->makeServer();

        $badResponse = new Response(403, [], 'Forbidden');
        $request = m::mock(RequestInterface::class);
        $exception = new BadResponseException('Bad response', $request, $badResponse);

        $server->http = m::mock(Client::class);
        $server->http->shouldReceive('post')->andThrow($exception);

        $tempCreds = $this->makeTemporaryCredentials();

        try {
            $server->getTokenCredentials($tempCreds, 'temp_id', 'verifier');
            $this->fail('Expected CredentialsException');
        } catch (CredentialsException $e) {
            $this->assertStringContainsString('403', $e->getMessage());
            $this->assertStringContainsString('Forbidden', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function getTokenCredentials_uses_guzzle_array_options_when_client_is_guzzle(): void
    {
        $server = $this->makeServer();
        $server->http = m::mock(Client::class);
        $server->http
            ->shouldReceive('post')
            ->once()
            ->with('test', m::on(function ($options) {
                return is_array($options)
                    && array_key_exists('headers', $options)
                    && array_key_exists('form_params', $options);
            }))
            ->andReturn($this->makeSuccessResponse());

        $tempCreds = $this->makeTemporaryCredentials();
        $server->getTokenCredentials($tempCreds, 'temp_id', 'verifier');
    }

    /**
     * @test
     */
    public function getTokenCredentials_passes_oauth_verifier_in_form_params(): void
    {
        $server = $this->makeServer();
        $server->http = m::mock(Client::class);
        $server->http
            ->shouldReceive('post')
            ->once()
            ->with('test', m::on(function ($options) {
                return $options['form_params'] === ['oauth_verifier' => 'my_verifier_code'];
            }))
            ->andReturn($this->makeSuccessResponse());

        $tempCreds = $this->makeTemporaryCredentials();
        $server->getTokenCredentials($tempCreds, 'temp_id', 'my_verifier_code');
    }

    /**
     * @test
     */
    public function getTokenCredentials_posts_to_urlTokenCredentials(): void
    {
        $server = $this->makeServer();
        $server->http = m::mock(Client::class);
        $server->http
            ->shouldReceive('post')
            ->once()
            ->with('test', m::any())
            ->andReturn($this->makeSuccessResponse());

        $tempCreds = $this->makeTemporaryCredentials();
        $server->getTokenCredentials($tempCreds, 'temp_id', 'verifier');
    }

    /**
     * @test
     */
    public function getTokenCredentials_sends_authorization_header(): void
    {
        $server = $this->makeServer();
        $server->http = m::mock(Client::class);
        $server->http
            ->shouldReceive('post')
            ->once()
            ->with('test', m::on(function ($options) {
                return isset($options['headers']['Authorization'])
                    && str_starts_with($options['headers']['Authorization'], 'OAuth ');
            }))
            ->andReturn($this->makeSuccessResponse());

        $tempCreds = $this->makeTemporaryCredentials();
        $server->getTokenCredentials($tempCreds, 'temp_id', 'verifier');
    }

    /**
     * @test
     */
    public function getTokenCredentials_legacy_branch_uses_chained_send(): void
    {
        $server = $this->makeServer();

        $response = $this->makeSuccessResponse('oauth_token=tok&oauth_token_secret=sec');

        $pendingRequest = m::mock('stdClass');
        $pendingRequest->shouldReceive('send')
            ->once()
            ->andReturn($response);

        $legacyClient = m::mock('stdClass');
        $legacyClient->shouldReceive('post')
            ->once()
            ->with('test', m::type('array'), ['oauth_verifier' => 'verifier'])
            ->andReturn($pendingRequest);

        $server->http = $legacyClient;

        $tempCreds = $this->makeTemporaryCredentials();
        $result = $server->getTokenCredentials($tempCreds, 'temp_id', 'verifier');

        $this->assertInstanceOf(TokenCredentials::class, $result['tokenCredentials']);
        $this->assertSame('tok', $result['tokenCredentials']->getIdentifier());
    }

    /**
     * @test
     */
    public function scopes_returns_self_for_fluent_chaining(): void
    {
        $server = $this->makeServer();

        $this->assertSame($server, $server->scopes(['read']));
    }

    /**
     * @test
     */
    public function scopes_merges_and_deduplicates(): void
    {
        $server = $this->makeServer();
        $server->scopes(['read', 'write']);
        $server->scopes(['read', 'admin']);

        $reflection = new \ReflectionClass($server);
        $prop = $reflection->getProperty('scopes');
        $prop->setAccessible(true);

        $this->assertSame(['read', 'write', 'admin'], array_values($prop->getValue($server)));
    }

    /**
     * @test
     */
    public function scopes_starts_empty(): void
    {
        $server = $this->makeServer();

        $reflection = new \ReflectionClass($server);
        $prop = $reflection->getProperty('scopes');
        $prop->setAccessible(true);

        $this->assertSame([], $prop->getValue($server));
    }

    /**
     * @test
     */
    public function with_returns_self_for_fluent_chaining(): void
    {
        $server = $this->makeServer();

        $this->assertSame($server, $server->with(['foo' => 'bar']));
    }

    /**
     * @test
     */
    public function with_replaces_parameters_instead_of_merging(): void
    {
        $server = $this->makeServer();
        $server->with(['foo' => 'bar']);
        $server->with(['baz' => 'qux']);

        $reflection = new \ReflectionClass($server);
        $prop = $reflection->getProperty('parameters');
        $prop->setAccessible(true);

        $this->assertSame(['baz' => 'qux'], $prop->getValue($server));
    }

    /**
     * @test
     */
    public function with_starts_with_empty_parameters(): void
    {
        $server = $this->makeServer();

        $reflection = new \ReflectionClass($server);
        $prop = $reflection->getProperty('parameters');
        $prop->setAccessible(true);

        $this->assertSame([], $prop->getValue($server));
    }

    /**
     * @test
     */
    public function formatScopes_joins_with_comma_separator(): void
    {
        $server = $this->makeServer();

        $this->assertSame('read,write,admin', $server->exposedFormatScopes(['read', 'write', 'admin'], ','));
    }

    /**
     * @test
     */
    public function formatScopes_joins_with_space_separator(): void
    {
        $server = $this->makeServer();

        $this->assertSame('read write admin', $server->exposedFormatScopes(['read', 'write', 'admin'], ' '));
    }

    /**
     * @test
     */
    public function formatScopes_returns_empty_string_for_empty_array(): void
    {
        $server = $this->makeServer();

        $this->assertSame('', $server->exposedFormatScopes([], ','));
    }

    /**
     * @test
     */
    public function formatScopes_handles_single_scope(): void
    {
        $server = $this->makeServer();

        $this->assertSame('read', $server->exposedFormatScopes(['read'], ','));
    }
}
