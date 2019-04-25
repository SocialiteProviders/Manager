<?php

namespace SocialiteProviders\Manager\Helpers;

use Closure;
use SocialiteProviders\Manager\Config;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;
use SocialiteProviders\Manager\Exception\MissingConfigException;

class ConfigRetriever implements ConfigRetrieverInterface
{
    /**
     * @var string
     */
    protected $providerName;

    /**
     * @var string
     */
    protected $providerIdentifier;

    /**
     * @var array
     */
    protected $servicesArray;

    /**
     * @var array
     */
    protected $additionalConfigKeys;

    /**
     * @param string $providerName
     * @param array  $additionalConfigKeys
     *
     * @return \SocialiteProviders\Manager\Contracts\ConfigInterface
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     */
    public function fromServices($providerName, array $additionalConfigKeys = [])
    {
        $this->providerName = $providerName;
        $this->getConfigFromServicesArray($providerName);

        $this->additionalConfigKeys = $additionalConfigKeys = array_unique($additionalConfigKeys + ['guzzle']);

        return new Config(
            $this->getFromServices('client_id'),
            $this->getFromServices('client_secret'),
            $this->getFromServices('redirect'),
            $this->getConfigItems($additionalConfigKeys, function ($key) {
                return $this->getFromServices(strtolower($key));
            })
        );
    }

    /**
     * @param array    $configKeys
     * @param \Closure $keyRetrievalClosure
     *
     * @return array
     */
    protected function getConfigItems(array $configKeys, Closure $keyRetrievalClosure)
    {
        return $this->retrieveItemsFromConfig($configKeys, $keyRetrievalClosure);
    }

    /**
     * @param array    $keys
     * @param \Closure $keyRetrievalClosure
     *
     * @return array
     */
    protected function retrieveItemsFromConfig(array $keys, Closure $keyRetrievalClosure)
    {
        $out = [];

        foreach ($keys as $key) {
            $out[$key] = $keyRetrievalClosure($key);
        }

        return $out;
    }

    /**
     * @param string $key
     *
     * @return string
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     */
    protected function getFromServices($key)
    {
        $keyExists = array_key_exists($key, $this->servicesArray);

        // ADDITIONAL value is empty
        if (!$keyExists && $this->isAdditionalConfig($key)) {
            return $key == 'guzzle' ? [] : null ;
        }

        // REQUIRED value is empty
        if (!$keyExists) {
            throw new MissingConfigException("Missing services entry for {$this->providerName}.$key");
        }

        return $this->servicesArray[$key];
    }

    /**
     * @param string $providerName
     *
     * @return array
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     */
    protected function getConfigFromServicesArray($providerName)
    {
        $configArray = config("services.{$providerName}");

        if (empty($configArray)) {
            // If we are running in console we should spoof values to make Socialite happy...
            if (app()->runningInConsole()) {
                $configArray = [
                    'client_id' => "{$this->providerIdentifier}_KEY",
                    'client_secret' => "{$this->providerIdentifier}_SECRET",
                    'redirect' => "{$this->providerIdentifier}_REDIRECT_URI",
                ];
            } else {
                throw new MissingConfigException("There is no services entry for $providerName");
            }
        }

        return $this->servicesArray = $configArray;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function isAdditionalConfig($key)
    {
        return in_array(strtolower($key), $this->additionalConfigKeys, true);
    }
}
