<?php
declare(strict_types=1);

namespace SocialiteProviders\Manager;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Config implements Contracts\ConfigInterface
{
    /**
     * @var array
     */
    protected $config;

    public function __construct(string $key, string $secret, string $callbackUri, array $additionalProviderConfig = [])
    {
        $this->config = array_merge([
            'client_id' => $key,
            'client_secret' => $secret,
            'redirect' => $this->formatRedirectUri($callbackUri),
        ], $additionalProviderConfig);
    }

    /**
     * Format the callback URI, resolving a relative URI if needed.
     *
     * @param string $callbackUri
     * @return string
     */
    protected function formatRedirectUri($callbackUri): string
    {
        return Str::startsWith($callbackUri, '/')
            ? URL::to($callbackUri)
            : $callbackUri;
    }

    public function get(): array
    {
        return $this->config;
    }
}
