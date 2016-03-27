<?php
namespace SocialiteProviders\Manager\Helpers;

use SocialiteProviders\Manager\Config;
use SocialiteProviders\Manager\Contracts\ConfigInterface;
use SocialiteProviders\Manager\Exception\MissingConfigException;

class ConfigRetrieverInterface implements \SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface
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
     * @return ConfigInterface
     * @throws MissingConfigException
     */
    public function fromEnv($providerIdentifier, array $additionalConfigKeys = [])
    {
        $this->providerIdentifier = $providerIdentifier;

        return new Config(
            $this->getFromEnv("KEY"),
            $this->getFromEnv("SECRET"),
            $this->getFromEnv("REDIRECT_URI"),
            $this->getConfigItems($additionalConfigKeys, function ($key) {
                return $this->getFromEnv($key);
            }));
    }

    /**
     * @param string $providerName
     * @param array  $additionalConfigKeys
     *
     * @return ConfigInterface
     */
    public function fromServices($providerName, array $additionalConfigKeys = [])
    {
        $this->providerName = $providerName;

        throw new \Exception('not implemented');
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
     * @return string
     * @throws MissingConfigException
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
     * @param $providerName
     *
     * @return array
     * @throws MissingConfigException
     */
    protected function getConfigFromServicesArray($providerName)
    {
        if (!$this->servicesArray) {
            $providerArray = app()->offsetGet('config')['services.'.$providerName];

            if (empty($providerArray)) {
                throw new MissingConfigException("There is no services entry for $providerName");
            }
        }

        return $this->servicesArray;
    }
}
