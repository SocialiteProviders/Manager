<?php
namespace AndyWendt\Socialite\Extender;

class SocialiteWasCalled
{
    /**
     * @param string $providerName 'meetup'
     * @param string $providerClass 'Your\Name\Space\ClassName'
     */
    public function extendSocialite($providerName, $providerClass)
    {
        $socialite = \App::make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            $providerName,
            function ($app) use ($socialite, $providerName, $providerClass) {
                $config = $app['config']['services.' . $providerName];
                return $socialite->buildProvider($providerClass, $config);
            }
        );
    }
}
