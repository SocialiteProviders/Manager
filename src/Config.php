<?php

namespace SocialiteProviders\Manager;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Config implements Contracts\ConfigInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Config constructor.
     *
     * @param  string  $key
     * @param  string  $secret
     * @param  string|callable  $callbackUri
     * @param  array  $additionalProviderConfig
     */
    public function __construct($key, $secret, $callbackUri, array $additionalProviderConfig = [])
    {
        $this->config = array_merge([
            'client_id'     => $key,
            'client_secret' => $secret,
            'redirect'      => $this->formatRedirectUri($callbackUri),
        ], $additionalProviderConfig);
    }

    /**
     * Format the callback URI, resolving a relative URI if needed.
     *
     * @param  string  $callbackUri
     * @return string
     */
    protected function formatRedirectUri($callbackUri)
    {
        $redirect = value($callbackUri);

        return Str::startsWith($redirect, '/')
                    ? URL::to($redirect)
                    : $redirect;
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->config;
    }
}
