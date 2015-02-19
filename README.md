# Socialite Providers Manager
An easy way to extend Laravel Socialite 

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
## Contents

- [Installation](#installation)
  - [Composer](#composer)
  - [Service Provider](#service-provider)
  - [Event and Provider Listeners](#event-and-provider-listeners)
    - [Available Providers](#available-providers)
  - [Usage](#usage)
- [Creating a Handler](#creating-a-handler)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->


## Installation

### Composer

```bash
composer require socialiteproviders/manager
```

### Service Provider

Add `SocialiteProviders\Manager\ServiceProvider` to your `providers[]` in `config\app.php`.

e.g.: 

```php
'providers' => [
    // a whole bunch of providers
    `SocialiteProviders\Manager\ServiceProvider`
];
```

### Event and Provider Listeners

Add `SocialiteProviders\Manager\SocialiteWasCalled` to your `listen[]` in `<app>/Providers/EventServiceProvider`.

[See also the Laravel docs about events](http://laravel.com/docs/5.0/events).

For example:
 
```php
/**
 * The event handler mappings for the application.
 *
 * @var array
 */
protected $listen = [
    `SocialiteProviders\Manager\SocialiteWasCalled` => [
        'Your\Name\Space\ProviderNameExtendSocialite@handle', 
        // This is where we define all of our ExtendSocialite listeners (i.e. new providers)
    ],
];
```

#### Available Providers

* [Meetup.com](https://github.com/SocialiteProviders/Meetup)
* You can also make your own or modify someone else's

### Usage

You should now be able to use it like so:

```php
return Socialite::with('providername')->redirect(); 
// replace providername with your provider name!
```


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
        $socialiteWasCalled->extendSocialite('providername', 'Your\Name\Space\ProviderName');
    }
}
```
