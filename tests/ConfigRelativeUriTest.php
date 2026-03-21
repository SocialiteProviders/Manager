<?php

namespace SocialiteProviders\Manager\Test;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\URL;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\Config;

class ConfigRelativeUriTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();

        $urlGenerator = m::mock(\Illuminate\Contracts\Routing\UrlGenerator::class);
        $this->urlGenerator = $urlGenerator;

        // Set up a minimal Facade application container
        $app = new \ArrayObject();
        $app['url'] = $urlGenerator;

        Facade::setFacadeApplication($app);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        parent::tearDown();
    }

    /** @var \Mockery\MockInterface */
    protected $urlGenerator;

    /**
     * @test
     */
    public function it_resolves_relative_uri_using_url_to(): void
    {
        $this->urlGenerator
            ->shouldReceive('to')
            ->with('/callback/oauth')
            ->once()
            ->andReturn('http://localhost/callback/oauth');

        $config = new Config('key', 'secret', '/callback/oauth');
        $result = $config->get();

        $this->assertSame('http://localhost/callback/oauth', $result['redirect']);
    }

    /**
     * @test
     */
    public function it_does_not_resolve_absolute_uri(): void
    {
        $this->urlGenerator->shouldNotReceive('to');

        $config = new Config('key', 'secret', 'https://example.com/callback');
        $result = $config->get();

        $this->assertSame('https://example.com/callback', $result['redirect']);
    }
}
