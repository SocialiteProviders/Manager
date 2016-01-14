<?php

namespace SocialiteProviders\Manager;

use Mockery as m;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function it_returns_a_config_array()
    {
        $key = 'key';
        $secret = 'secret';
        $callbackUri = 'uri';

        $result = [
            'client_id' => $key,
            'client_secret' => $secret,
            'redirect' => $callbackUri,
        ];

        $config = new Config($key, $secret, $callbackUri);

        $this->assertSame($result, $config->get());
    }
}
