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
        $configRetriever = new ConfigRetriever();

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
        $configRetriever = new ConfigRetriever();

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
            'client_id' => $key,
            'client_secret' => $secret,
            'redirect' => $uri,
            'additional' => $additionalConfigItem,
        ];
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn($config);
        $configRetriever = new ConfigRetriever();

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
            'client_id' => $key,
            'client_secret' => $secret,
            'redirect' => $uri,
            'additional' => $additionalConfigItem,
            'guzzle' => ['verify' => false],
        ];
        self::$functions
            ->shouldReceive('config')
            ->with("services.{$providerName}")
            ->once()
            ->andReturn($config);
        $configRetriever = new ConfigRetriever();

        $result = $configRetriever->fromServices($providerName, ['additional'])->get();

        $this->assertSame($key, $result['client_id']);
        $this->assertSame($secret, $result['client_secret']);
        $this->assertSame($uri, $result['redirect']);
        $this->assertSame($additionalConfigItem, $result['additional']);
        $this->assertSame(['verify' => false], $result['guzzle']);
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
    return new applicationStub();
}

class applicationStub
{
    public function runningInConsole(): bool
    {
        return false;
    }
}
