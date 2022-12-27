# CodeIgniter Signed URL

Sign URLs in your CodeIgniter 4 application. Prevent manual URL manipulation or auto expiry URLs.

[![PHPUnit](https://github.com/michalsn/codeigniter-signed-url/actions/workflows/phpunit.yml/badge.svg)](https://github.com/michalsn/codeigniter-htmx/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/michalsn/codeigniter-signed-url/actions/workflows/phpstan.yml/badge.svg)](https://github.com/michalsn/codeigniter-htmx/actions/workflows/phpstan.yml)
[![Deptrac](https://github.com/michalsn/codeigniter-signed-url/actions/workflows/deptrac.yml/badge.svg)](https://github.com/michalsn/codeigniter-htmx/actions/workflows/deptrac.yml)
[![Coverage Status](https://coveralls.io/repos/github/michalsn/codeigniter-signed-url/badge.svg?branch=develop)](https://coveralls.io/github/michalsn/codeigniter-htmx?branch=develop)


## Installation

    composer require michalsn/codeigniter-signed-url

## Overview

We can sign URLs very easy with two main methods that act similar to the helper functions known from CodeIgniter's URL helper.

```php
echo signedurl()->siteUrl('controller/method?query=string');
// https://example.com/controller/method?query=string&signature=signature-goes-here
```

```php
echo signedurl()->setExpiration(DAY * 2)->urlTo('namedRoute', 12);
// https://example.com/controller/method/12?expiration=1671980371&signature=signature-goes-here
```

## Configuration

To make changes to the config file, we have to have our copy in the `app/Config/SignedUrl.php`. Luckily, this package comes with handy command that will make this easy.

When we run:

    php spark signedurl:publish

We will get our copy ready for modifications.

**Warning**

Be aware that changing CodeIgniter's `encryption key` will instantly invalidate all the generated URLs.

#### $expirationTime

This setting allows us to set a fixed time after which the signed URL will expire.
It's number of seconds in unix timestamp that will be added to the current date.

By default, this is set to `null`.

#### $algorithm

This setting allows us to set algorithm that will be used during signing the URLs.

You can see the list of all available options when running command:

    php spark signedurl:algorithms

**Warning**

Changing this value without including the used algorithm key to the query string will invalidate all the generated URLs instantly.

#### $expirationKey

This is the name of the query string key, which will be responsible for storing the time after which the URL will expire.

Whatever the name you will choose, treat it as a restricted name and don't use it as a part Query String in your code.

By default, this is set to `expires`.

#### $signatureKey

This is the name of the query string key, which will be responsible for storing the signature by which the validity of the entire URL will be checked.

Whatever the name you will choose, treat it as a restricted name and don't use it as a part of the Query String in your code.

By default, this is set to `signature`.

#### $algorithmKey

This is the name of the query string key, which will be responsible for storing the algorithm by which the validity of the entire URL will be checked.

Whatever the name you will choose, treat it as a restricted name and don't use it as a part of the Query String in your code.

By default, this is set to `algorithm`.

#### $includeAlgorithmKey

This setting determines if the algorithm will be included to the query string of the generated URL.

By default, this is set to `false`.

#### $redirect

This setting is used in the Filter to determine whether we will redirect user to the previous page with the `error`, when URL will not be valid or expired.

By default, this is set to `false`.

#### $show404

This setting is used in the Filter to determine whether we will show a 404 page, when URL will not be valid or expired.

By default, this is set to `false`.

## Methods

#### siteUrl()

This method is similar to the standard `site_url`, but it produces signed URL.

```php
service('signedurl')->siteUrl('controller/method');
```

#### urlTo()

This method is similar to the standard `url_to`, but it produces signed URL.

```php
service('signedurl')->urlTo('namedRoute', 'param');
```

#### sign()

With this method we can sign URI. Usually you won't be using this method directly, since it is used by other methods.

```php
service('signedurl')->sign($uri);
```

By default `$expirationTime` is set to `null`. If you want the URLs to always be valid for a certain period of time, you can set time in the `$expirationTime` variable in the configuration file.

#### verify()

With this method we can verify if given URL is properly signed and not expired if expiration timestamp was set during URL creation.

```php
service('signedurl')->verify($request);
```

The URL verification may take place automatically via Filter class, but you can also make it happen in your Controller instead. The choice is up to you.

#### setExpiration()

With this method we can set temporary value for expiration. Value set here will be resetted after calling method: `siteUrl()`, `urlTo()` or `sign()`.

```php
service('signedurl')->setExpiration(DAY)->siteUrl('url');
```

## Helpers

#### signedurl()

This function returns the `SignedUrl` class instance.

```php
signedurl()->setExpiration(DAY)->siteUrl('controller/method');
```

## Filters

To validate signed URLs we can use build in filter. We can enable it in two simple steps.

1. We have to add our filter to the `$aliases` array.
2. And then define when filter should be fired up. In the example below we will assume it will be used when the first segment of the url will contain `signed-urls` string.

```php
// app/Config/Filters.php
<?php

...

use Michalsn\CodeIgniterSignedUrl\Filters\SignedUrl;

class Filters extends BaseConfig
{
    ...

    public $aliases = [
        ...
        'signedurl' => SignedUrl::class
    ];

    ...

    public $filters = [
        'signedurl' => ['before' => ['signed-urls/*']],
    ];
```

By default, this filter will throw `SignedUrlException` when the URL won't be signed or will be expired. But there are other options, and we can enable them by editing the config file:

* We can redirect to the previous page
* Or show 404 page

More info you can find in the Config section.

Remember, that if the filter implementation doesn't suit you, you can always create your own, which will behave differently upon an error. You can also not use the filter at all and make the check in the controller.
