<?php

namespace SocialiteProviders\Manager\Helpers;

use SocialiteProviders\Manager\Config;
use SocialiteProviders\Manager\Exception\MissingConfigException;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;

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
     * @param string $providerIdentifier
     * @param array  $additionalConfigKeys
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     *
     * @return \SocialiteProviders\Manager\Contracts\ConfigInterface
     */
    public function fromEnv($providerIdentifier, array $additionalConfigKeys = [])
    {
        $this->providerIdentifier = $providerIdentifier;
        $this->additionalConfigKeys = $additionalConfigKeys;

        return new Config(
            $this->getFromEnv('KEY'),
            $this->getFromEnv('SECRET'),
            $this->getFromEnv('REDIRECT_URI'),
            $this->getConfigItems($additionalConfigKeys, function ($key) {
                return $this->getFromEnv(strtoupper($key));
            }));
    }

    /**
     * @param string $providerName
     * @param array  $additionalConfigKeys
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     *
     * @return \SocialiteProviders\Manager\Contracts\ConfigInterface
     */
    public function fromServices($providerName, array $additionalConfigKeys = [])
    {
        $this->providerName = $providerName;
        $this->getConfigFromServicesArray($providerName);

        $this->additionalConfigKeys = $additionalConfigKeys;

        return new Config(
            $this->getFromServices('client_id'),
            $this->getFromServices('client_secret'),
            $this->getFromServices('redirect'),
            $this->getConfigItems($additionalConfigKeys, function ($key) {
                return $this->getFromServices(strtolower($key));
            }));
    }

    /**
     * @param array    $configKeys
     * @param \Closure $keyRetrievalClosure
     *
     * @return array
     */
    protected function getConfigItems(array $configKeys, \Closure $keyRetrievalClosure)
    {
        if (count($configKeys) < 1) {
            return [];
        }

        return $this->retrieveItemsFromConfig($configKeys, $keyRetrievalClosure);
    }

    /**
     * @param array    $keys
     * @param \Closure $keyRetrievalClosure
     *
     * @return array
     */
    protected function retrieveItemsFromConfig(array $keys, \Closure $keyRetrievalClosure)
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
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     *
     * @return string
     */
    protected function getFromServices($key)
    {
        $keyExists = array_key_exists($key, $this->servicesArray);

        // ADDITIONAL value is empty
        if (! $keyExists && $this->isAdditionalConfig($key)) {
            return;
        }

        // REQUIRED value is empty
        if (! $keyExists) {
            throw new MissingConfigException("Missing services entry for {$this->providerName}.$key");
        }

        return $this->servicesArray[$key];
    }

    /**
     * @param string $key
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     *
     * @return string
     */
    protected function getFromEnv($key)
    {
        $providerKey = "{$this->providerIdentifier}_{$key}";
        $item = env($providerKey);

        // ADDITIONAL value is empty
        if (empty($item) && $this->isAdditionalConfig($key)) {
            return;
        }

        // REQUIRED value is empty
        if (empty($item)) {
            // If we are running in console we should spoof values to make Socialite happy...
            if (! app()->runningInConsole()) {
                throw new MissingConfigException("Configuration for $providerKey is missing.");
            }
            $item = $providerKey;
        }

        return $item;
    }

    /**
     * @param string $providerName
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     *
     * @return array
     */
    protected function getConfigFromServicesArray($providerName)
    {
        /** @var array $configArray */
        $configArray = config("services.$providerName");

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

        $this->servicesArray = $configArray;

        return $this->servicesArray;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function isAdditionalConfig($key)
    {
        return in_array(strtolower($key), $this->additionalConfigKeys);
    }
}
