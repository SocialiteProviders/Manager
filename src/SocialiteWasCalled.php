<?php
namespace SocialiteProviders\Manager;

class SocialiteWasCalled
{
    /**
     * @param string $providerName   'meetup'
     * @param string $providerClass  'Your\Name\Space\ClassNameProvider'
     * @param string $providerServer 'Your\Name\Space\ClassNameServer'
     */
    public function extendSocialite($providerName, $providerClass, $providerServer = null)
    {
        $socialite = app()->make('Laravel\Socialite\Contracts\Factory');

        $socialite->extend(
            $providerName,
            function ($app) use ($socialite, $providerName, $providerClass, $providerServer) {
                $config = $app['config']['services.'.$providerName];

                if (!empty($providerServer)) {
                    return new $providerClass(
                        $app['request'],
                        new $providerServer([
                            'identifier'   => $config['client_id'],
                            'secret'       => $config['client_secret'],
                            'callback_uri' => $config['redirect'],
                        ])
                    );
                }

                return $socialite->buildProvider($providerClass, $config);
            }
        );
    }
}
