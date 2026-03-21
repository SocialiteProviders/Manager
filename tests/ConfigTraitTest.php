<?php

namespace SocialiteProviders\Manager\Test;

use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\Config;
use SocialiteProviders\Manager\ConfigTrait;

class ConfigTraitUser
{
    use ConfigTrait;

    public $clientId;
    public $clientSecret;
    public $redirectUrl;

    public function callGetConfig($key = null, $default = null)
    {
        return $this->getConfig($key, $default);
    }
}

class ConfigTraitTest extends TestCase
{
    protected function makeConfigUser(array $configArray = []): ConfigTraitUser
    {
        $user = new ConfigTraitUser;
        $config = new Config(
            $configArray['client_id'] ?? 'id',
            $configArray['client_secret'] ?? 'secret',
            $configArray['redirect'] ?? 'redirect',
            array_diff_key($configArray, array_flip(['client_id', 'client_secret', 'redirect']))
        );
        $user->setConfig($config);

        return $user;
    }

    /**
     * @test
     */
    public function setConfig_sets_config_array_and_returns_self(): void
    {
        $user = new ConfigTraitUser;
        $config = new Config('id', 'secret', 'redirect');
        $result = $user->setConfig($config);

        $this->assertSame($user, $result);
    }

    /**
     * @test
     */
    public function setConfig_populates_clientId_clientSecret_redirectUrl(): void
    {
        $user = new ConfigTraitUser;
        $config = new Config('my_id', 'my_secret', 'my_redirect');
        $user->setConfig($config);

        $this->assertSame('my_id', $user->clientId);
        $this->assertSame('my_secret', $user->clientSecret);
        $this->assertSame('my_redirect', $user->redirectUrl);
    }

    /**
     * @test
     */
    public function additionalConfigKeys_returns_empty_array_by_default(): void
    {
        $this->assertSame([], ConfigTraitUser::additionalConfigKeys());
    }

    /**
     * @test
     */
    public function getConfig_returns_full_config_when_no_key(): void
    {
        $user = $this->makeConfigUser([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => 'redirect',
        ]);

        $result = $user->callGetConfig();

        $this->assertIsArray($result);
        $this->assertSame('id', $result['client_id']);
        $this->assertSame('secret', $result['client_secret']);
        $this->assertSame('redirect', $result['redirect']);
    }

    /**
     * @test
     */
    public function getConfig_returns_value_for_existing_key(): void
    {
        $user = $this->makeConfigUser([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => 'redirect',
            'tenant' => 'my_tenant',
        ]);

        $this->assertSame('my_tenant', $user->callGetConfig('tenant'));
    }

    /**
     * @test
     */
    public function getConfig_returns_default_for_missing_key(): void
    {
        $user = $this->makeConfigUser();

        $this->assertSame('fallback', $user->callGetConfig('nonexistent', 'fallback'));
    }

    /**
     * @test
     */
    public function getConfig_returns_default_for_empty_string_value(): void
    {
        $user = $this->makeConfigUser([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => 'redirect',
            'empty_val' => '',
        ]);

        // Current behavior: empty() treats '' as empty, so default is returned
        $this->assertSame('default', $user->callGetConfig('empty_val', 'default'));
    }

    /**
     * @test
     */
    public function getConfig_returns_default_for_zero_value(): void
    {
        $user = $this->makeConfigUser([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => 'redirect',
            'zero_val' => 0,
        ]);

        // Current behavior: empty() treats 0 as empty, so default is returned
        $this->assertSame('default', $user->callGetConfig('zero_val', 'default'));
    }

    /**
     * @test
     */
    public function getConfig_returns_default_for_false_value(): void
    {
        $user = $this->makeConfigUser([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => 'redirect',
            'false_val' => false,
        ]);

        // Current behavior: empty() treats false as empty, so default is returned
        $this->assertSame('default', $user->callGetConfig('false_val', 'default'));
    }

    /**
     * @test
     */
    public function getConfig_returns_null_as_default_when_no_default_specified(): void
    {
        $user = $this->makeConfigUser();

        $this->assertNull($user->callGetConfig('nonexistent'));
    }

    /**
     * @test
     */
    public function getConfig_returns_full_config_when_key_is_null(): void
    {
        $user = $this->makeConfigUser();
        $result = $user->callGetConfig(null);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('client_id', $result);
    }

    /**
     * @test
     */
    public function getConfig_returns_truthy_values_correctly(): void
    {
        $user = $this->makeConfigUser([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => 'redirect',
            'count' => 42,
        ]);

        $this->assertSame(42, $user->callGetConfig('count'));
    }

    /**
     * @test
     */
    public function getConfig_returns_array_values_correctly(): void
    {
        $guzzle = ['verify' => false, 'timeout' => 30];
        $user = $this->makeConfigUser([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'redirect' => 'redirect',
            'guzzle' => $guzzle,
        ]);

        $this->assertSame($guzzle, $user->callGetConfig('guzzle'));
    }
}
