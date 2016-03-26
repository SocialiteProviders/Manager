<?php

namespace SocialiteProviders\Manager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SocialiteProviders\SocialiteProvidersManager
 */
class SocialiteProvider extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \SocialiteProviders\Contracts\Factory::class;
    }
}
