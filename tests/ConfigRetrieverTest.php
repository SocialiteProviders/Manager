<?php

namespace SocialiteProviders\Manager\Test;

use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\Exception\MissingConfigException;
use SocialiteProviders\Manager\Helpers\ConfigRetriever;

class ConfigRetrieverTest extends TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     */
    public function it_throws_if_there_is_a_problem_with_the_services_config(): void
    {
        $this->expectExceptionObject(new MissingConfigException('There is no services entry for test'));

        $providerName = 'test';
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn(null);
        $configRetriever = new ConfigRetriever;

        $configRetriever->fromServices($providerName)->get();
    }

    /**
     * @test
     */
    public function it_throws_if_there_are_missing_items_in_the_services_config(): void
    {
        $this->expectExceptionObject(new MissingConfigException('There is no services entry for test'));

        $providerName = 'test';
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn([]);
        $configRetriever = new ConfigRetriever;

        $configRetriever->fromServices($providerName)->get();
    }

    /**
     * @test
     */
    public function it_retrieves_a_config_from_the_services(): void
    {
        $providerName = 'test';
        $key = 'key';
        $secret = 'secret';
        $uri = 'uri';
        $additionalConfigItem = 'test';
        $config = [
            'client_id'     => $key,
            'client_secret' => $secret,
            'redirect'      => $uri,
            'additional'    => $additionalConfigItem,
        ];
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn($config);
        $configRetriever = new ConfigRetriever;

        $result = $configRetriever->fromServices($providerName, ['additional'])->get();

        $this->assertSame($key, $result['client_id']);
        $this->assertSame($secret, $result['client_secret']);
        $this->assertSame($uri, $result['redirect']);
        $this->assertSame($additionalConfigItem, $result['additional']);
    }

    /**
     * @test
     */
    public function it_retrieves_a_config_from_the_services_with_guzzle(): void
    {
        $providerName = 'test';
        $key = 'key';
        $secret = 'secret';
        $uri = 'uri';
        $additionalConfigItem = 'test';
        $config = [
            'client_id'     => $key,
            'client_secret' => $secret,
            'redirect'      => $uri,
            'additional'    => $additionalConfigItem,
            'guzzle'        => ['verify' => false],
        ];
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn($config);
        $configRetriever = new ConfigRetriever;

        $result = $configRetriever->fromServices($providerName, ['additional'])->get();

        $this->assertSame($key, $result['client_id']);
        $this->assertSame($secret, $result['client_secret']);
        $this->assertSame($uri, $result['redirect']);
        $this->assertSame($additionalConfigItem, $result['additional']);
        $this->assertSame(['verify' => false], $result['guzzle']);
    }

    /**
     * @test
     */
    public function it_spoofs_config_when_running_in_console_and_config_is_empty(): void
    {
        \SocialiteProviders\Manager\Helpers\applicationStub::$runningInConsole = true;

        $providerName = 'test';
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn(null);
        $configRetriever = new ConfigRetriever;

        $result = $configRetriever->fromServices($providerName)->get();

        $this->assertStringContainsString('_KEY', $result['client_id']);
        $this->assertStringContainsString('_SECRET', $result['client_secret']);
        $this->assertStringContainsString('_REDIRECT_URI', $result['redirect']);

        \SocialiteProviders\Manager\Helpers\applicationStub::$runningInConsole = false;
    }

    /**
     * @test
     */
    public function it_returns_empty_array_for_missing_guzzle_config(): void
    {
        $providerName = 'test';
        $config = [
            'client_id'     => 'key',
            'client_secret' => 'secret',
            'redirect'      => 'uri',
        ];
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn($config);
        $configRetriever = new ConfigRetriever;

        $result = $configRetriever->fromServices($providerName)->get();

        $this->assertSame([], $result['guzzle']);
    }

    /**
     * @test
     */
    public function it_returns_null_for_missing_additional_config_key(): void
    {
        $providerName = 'test';
        $config = [
            'client_id'     => 'key',
            'client_secret' => 'secret',
            'redirect'      => 'uri',
        ];
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn($config);
        $configRetriever = new ConfigRetriever;

        $result = $configRetriever->fromServices($providerName, ['tenant_id'])->get();

        $this->assertNull($result['tenant_id']);
    }

    /**
     * @test
     */
    public function it_throws_for_missing_required_key(): void
    {
        $this->expectExceptionObject(new MissingConfigException('Missing services entry for test.client_secret'));

        $providerName = 'test';
        $config = [
            'client_id' => 'key',
            // client_secret is missing
            'redirect'  => 'uri',
        ];
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn($config);
        $configRetriever = new ConfigRetriever;

        $configRetriever->fromServices($providerName)->get();
    }

    /**
     * @test
     */
    public function it_deduplicates_guzzle_in_additional_config_keys(): void
    {
        $providerName = 'test';
        $config = [
            'client_id'     => 'key',
            'client_secret' => 'secret',
            'redirect'      => 'uri',
            'guzzle'        => ['timeout' => 10],
        ];
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn($config);
        $configRetriever = new ConfigRetriever;

        // Pass 'guzzle' explicitly — it should be deduplicated, not appear twice
        $result = $configRetriever->fromServices($providerName, ['guzzle'])->get();

        $this->assertSame(['timeout' => 10], $result['guzzle']);
    }
}

namespace SocialiteProviders\Manager\Helpers;

use SocialiteProviders\Manager\Test\ConfigRetrieverTest;

function env($key)
{
    return ConfigRetrieverTest::$functions->env($key);
}

function config($key)
{
    return ConfigRetrieverTest::$functions->config($key);
}

function app()
{
    return new applicationStub;
}

class applicationStub
{
    public static bool $runningInConsole = false;

    public function runningInConsole(): bool
    {
        return self::$runningInConsole;
    }
}
