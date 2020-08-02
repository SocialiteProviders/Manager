<?php

namespace SocialiteProviders\Manager\Test;

use Mockery as m;
use SocialiteProviders\Manager\Exception\MissingConfigException;
use SocialiteProviders\Manager\Helpers\ConfigRetriever;

class ConfigRetrieverTest extends \PHPUnit_Framework_TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     */
    public function it_throws_if_there_is_a_problem_with_the_services_config()
    {
        $this->expectException(MissingConfigException::class);

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
    public function it_throws_if_there_are_missing_items_in_the_services_config()
    {
        $this->expectException(MissingConfigException::class);

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
    public function it_retrieves_a_config_from_the_services()
    {
        $providerName = 'test';
        $key = 'key';
        $secret = 'secret';
        $uri = 'uri';
        $additionalConfigItem = 'test';
        $guzzle = [
            'proxy' => 'test',
        ];
        $config = [
            'client_id' => $key,
            'client_secret' => $secret,
            'redirect' => $uri,
            'additional' => $additionalConfigItem,
            'guzzle' => $guzzle
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
        $this->assertSame($guzzle, $result['guzzle']);
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
    public function runningInConsole()
    {
        return false;
    }
}
