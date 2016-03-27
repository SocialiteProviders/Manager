<?php
namespace SocialiteProviders\Manager\Test;

use \Mockery as m;
use SocialiteProviders\Manager\Helpers\ConfigRetriever;

class ConfigRetrieverTest extends \PHPUnit_Framework_TestCase
{
    use ManagerTestTrait;

    /**
     * @test
     */
    public function it_retrieves_a_config_from_the_env()
    {
        $providerIdentifier = 'TEST';
        self::$functions->shouldReceive('env')->with("{$providerIdentifier}_KEY")->once()->andReturn('key');
        self::$functions->shouldReceive('env')->with("{$providerIdentifier}_SECRET")->once()->andReturn('secret');
        self::$functions->shouldReceive('env')->with("{$providerIdentifier}_REDIRECT_URI")->once()->andReturn('uri');
        $configRetriever = new ConfigRetriever();
        $result = $configRetriever->fromEnv('TEST');

        var_dump($result->get());
    }
}


namespace SocialiteProviders\Manager\Helpers;

use SocialiteProviders\Manager\Test\ConfigRetrieverTest;

function env($key)
{
    return ConfigRetrieverTest::$functions->env($key);
}

