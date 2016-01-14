# Socialite Providers Manager

[![Build Status](https://travis-ci.org/SocialiteProviders/Manager.svg)](https://travis-ci.org/SocialiteProviders/Manage) 
[![Code Coverage](https://scrutinizer-ci.com/g/SocialiteProviders/Manager/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SocialiteProviders/Manager/?branch=master) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SocialiteProviders/Manager/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SocialiteProviders/Manager/?branch=master) 
[![Latest Stable Version](https://poser.pugx.org/socialiteproviders/manager/v/stable.svg)](https://packagist.org/packages/socialiteproviders/manager) 
[![Total Downloads](https://poser.pugx.org/socialiteproviders/manager/downloads.svg)](https://packagist.org/packages/socialiteproviders/manager) 
[![Latest Unstable Version](https://poser.pugx.org/socialiteproviders/manager/v/unstable.svg)](https://packagist.org/packages/socialiteproviders/manager) 
[![License](https://poser.pugx.org/socialiteproviders/manager/license.svg)](https://packagist.org/packages/socialiteproviders/manager) 
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ddb2f0df-6d85-431c-8e68-6164b08dd852/small.png)](https://insight.sensiolabs.com/projects/ddb2f0df-6d85-431c-8e68-6164b08dd852)

## About

A package for Laravel Socialite that allows you to easily add new providers or override current providers.  
  
### Benefits

* You will have access to all of the providers that you load in using the manager.
* Instantiation is deferred until Socialite is called
* You can override current providers
* You can create new providers

## Available Providers

* See the [SocialiteProviders](http://socialiteproviders.github.io/) list
* You can also make your own or modify someone else's


## Reference

* [Laravel docs about events](http://laravel.com/docs/5.0/events)
* [Laracasts video on events in Laravel 5](https://laracasts.com/lessons/laravel-5-events)
* [Laravel Socialite Docs](http://laravel.com/docs/5.0/authentication#social-authentication)  
* [Laracasts Socialite video](https://laracasts.com/series/whats-new-in-laravel-5/episodes/9)


## Creating a Handler

Below is an example handler.  You need to add this full class name to the `listen[]` in the `EventServiceProvider`.

* [See also the Laravel docs about events](http://laravel.com/docs/5.0/events).
* `providername` is the name of the provider such as `meetup`.
* You will need to change your the namespacing and class names of course.  


```php
namespace Your\Name\Space;

use SocialiteProviders\Manager\SocialiteWasCalled;

class ProviderNameExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('providername', \Your\Name\Space\Provider::class);
    }
}
```


## Creating a Provider

* Look at the already created [providers](http://socialiteproviders.github.io/) for inspiration.
* [See this article on Medium](https://medium.com/@morrislaptop/adding-auth-providers-to-laravel-socialite-ca0335929e42)

## Overriding a Built-in Provider

You can easily override a built-in `laravel/socialite` provider by creating a new one with exactly the same name (i.e. 'facebook').


## Dynamically Passing a Config

You can dynamically pass a config by using:
```
$key = 'SocialiteProviders.config.<provider_name>';
$config = new \SocialiteProviders\Manager\Config('key', 'secret', 'callbackUri');
$this->app->instance($key, $config)
```

**You must call this before you run any Socialite methods.**

## Getting the Access Token Response Body

Laravel Socialite by default only allows access to the `access_token`.  Which can be accessed 
via the `\Laravel\Socialite\User->token` public property.  Sometimes you need access to the whole response body which
may contain items such as a `refresh_token`.  

To make this possible, the OAuth2 provider class needs to extend `\SocialiteProviders\Manager\OAuth2\AbstractProvider`.

Currently, not all providers in the Socialite Providers have this implemented.  If you need this, submit and issue for 
the specific provider.

For the repositories that do support this, you can access it from the user object like so: `$user->accessTokenResponseBody`
