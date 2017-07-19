<?php

namespace SocialiteProviders\Manager\Test;

use SocialiteProviders\Manager\Helpers\ConfigRetriever;

class ConfigRetrieverTest extends \PHPUnit_Framework_TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     * @expectedException \SocialiteProviders\Manager\Exception\MissingConfigException
     */
    public function it_throws_if_there_is_a_problem_with_the_env_config()
    {
        $providerIdentifier = 'TEST';

        self::$functions->shouldReceive('env')->with("{$providerIdentifier}_KEY")->once()->andReturn(null);

        $configRetriever = new ConfigRetriever();
        $configRetriever->fromEnv('TEST')->get();
    }

    /**
     * @test
     * @expectedException \SocialiteProviders\Manager\Exception\MissingConfigException
     */
    public function it_throws_if_there_is_a_problem_with_the_services_config()
    {
        $providerName = 'test';

        self::$functions->shouldReceive('config')->with("services.$providerName")->once()->andReturn(null);
        $configRetriever = new ConfigRetriever();
        $configRetriever->fromServices($providerName)->get();
    }

    /**
     * @test
     * @expectedException \SocialiteProviders\Manager\Exception\MissingConfigException
     */
    public function it_throws_if_there_are_missing_items_in_the_services_config()
    {
        $providerName = 'test';

        self::$functions->shouldReceive('config')->with("services.$providerName")->once()->andReturn([]);
        $configRetriever = new ConfigRetriever();
        $configRetriever->fromServices($providerName)->get();
    }

    /**
     * @test
     */
    public function it_retrieves_a_config_from_the_env()
    {
        $providerIdentifier = 'TEST';
        $key = 'key';
        $secret = 'secret';
        $uri = 'uri';
        $additionalConfigItem = 'test';

        self::$functions->shouldReceive('env')->with("{$providerIdentifier}_KEY")->once()->andReturn($key);
        self::$functions->shouldReceive('env')->with("{$providerIdentifier}_SECRET")->once()->andReturn($secret);
        self::$functions->shouldReceive('env')->with("{$providerIdentifier}_REDIRECT_URI")->once()->andReturn($uri);
        self::$functions->shouldReceive('env')->with("{$providerIdentifier}_ADDITIONAL")->once()->andReturn($additionalConfigItem);
        $configRetriever = new ConfigRetriever();
        $result = $configRetriever->fromEnv('TEST', ['additional'])->get();

        $this->assertEquals($key, $result['client_id']);
        $this->assertEquals($secret, $result['client_secret']);
        $this->assertEquals($uri, $result['redirect']);
        $this->assertEquals($additionalConfigItem, $result['additional']);
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

        $config = [
            'client_id' => $key,
            'client_secret' => $secret,
            'redirect' => $uri,
            'additional' => $additionalConfigItem,
        ];

        self::$functions->shouldReceive('config')->with("services.$providerName")->once()->andReturn($config);
        $configRetriever = new ConfigRetriever();
        $result = $configRetriever->fromServices($providerName, ['additional'])->get();

        $this->assertEquals($key, $result['client_id']);
        $this->assertEquals($secret, $result['client_secret']);
        $this->assertEquals($uri, $result['redirect']);
        $this->assertEquals($additionalConfigItem, $result['additional']);
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
