<?php

namespace SocialiteProviders\Manager;

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

    /**
     * @test
     */
    public function it_allows_additional_config_items()
    {
        $key = 'key';
        $secret = 'secret';
        $callbackUri = 'uri';

        $result = [
            'client_id'     => $key,
            'client_secret' => $secret,
            'redirect'      => $callbackUri,
            'additional'    => true,
        ];

        $additional = ['additional' => true];

        $config = new Config($key, $secret, $callbackUri, $additional);

        $this->assertSame($result, $config->get());
    }
}
