<?php

namespace SocialiteProviders\Manager\Helpers;

use SocialiteProviders\Manager\Config;
use SocialiteProviders\Manager\Contracts\ConfigInterface;
use SocialiteProviders\Manager\Exception\MissingConfigException;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;

class ConfigRetriever implements ConfigRetrieverInterface
{
    /**
     * @var string
     */
    private $providerName;

    /**
     * @var string
     */
    private $providerIdentifier;

    /**
     * @var array
     */
    private $servicesArray;

    /**
     * @param string $providerIdentifier
     * @param array  $additionalConfigKeys
     *
     * @throws MissingConfigException
     *
     * @return ConfigInterface
     */
    public function fromEnv($providerIdentifier, array $additionalConfigKeys = [])
    {
        $this->providerIdentifier = $providerIdentifier;

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
     * @throws MissingConfigException
     *
     * @return ConfigInterface
     */
    public function fromServices($providerName, array $additionalConfigKeys = [])
    {
        $this->providerName = $providerName;
        $this->getConfigFromServicesArray($providerName);

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
    private function getConfigItems(array $configKeys, \Closure $keyRetrievalClosure)
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
    private function retrieveItemsFromConfig(array $keys, \Closure $keyRetrievalClosure)
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
     * @throws MissingConfigException
     *
     * @return string
     */
    private function getFromServices($key)
    {
        if (!array_key_exists($key, $this->servicesArray)) {
            throw new MissingConfigException("Missing services entry for {$this->providerName}.$key");
        }

        return $this->servicesArray[$key];
    }

    /**
     * @param string $key
     *
     * @throws MissingConfigException
     *
     * @return string
     */
    private function getFromEnv($key)
    {
        $providerKey = "{$this->providerIdentifier}_{$key}";
        $item = env($providerKey);

        if (empty($item)) {
            throw new MissingConfigException("Configuration for $providerKey is missing.");
        }

        return $item;
    }

    /**
     * @param string $providerName
     *
     * @throws MissingConfigException
     *
     * @return array
     */
    protected function getConfigFromServicesArray($providerName)
    {
        /** @var array $configArray */
        $configArray = config('services.'.$providerName);

        if (empty($configArray)) {
            throw new MissingConfigException("There is no services entry for $providerName");
        }

        $this->servicesArray = $configArray;

        return $this->servicesArray;
    }
}
