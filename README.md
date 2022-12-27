# CodeIgniter Signed URL

Sign URLs in your CodeIgniter 4 applications.

Why do I may need this?

* To prevent manual URL manipulation
* Or auto expiry URLs

## Installation

    composer require michalsn/codeigniter-signed-url

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

#### sign()

With this method we can sign URL and set its expiration date. Usually you won't be using this method directly, since it is used by other helper methods that you're probably used to use.

```php
service('signedurl')->sign($uri, $expirationTime);
```

By default `$expirationTime` is set to `null`. If you want the URLs to always be valid for a certain period of time, you can set time in the `$expirationTime` variable in the configuration file.

#### verify()

With this method we can verify if given URL is properly signed and not expired if expiration timestamp was set during URL creation.

```php
service('signedurl')->verify($request);
```

The URL verification may take place automatically via Filter class, but you can also make it happen in your Controller instead. The choice is up to you.

## Helpers

#### signed_site_url()

This helper is similar to standard `site_url()`, except that in the second parameter you can set expiration date. It's number of seconds since **now** in Unix timestamp.
```php
signed_site_url('controller/method');
// https://example.com/controller/method?signature=TAxnWuXpqnKJ0C5A8nCedWYFvpw
```

```php
signed_site_url('controller/method', DAY);
// https://example.com/controller/method?expires=1672079033&signature=2XAKiFWYBPambJ7djstsHkUSJvk
```

#### signed_anchor()

This helper is similar to standard `anchor()`, except that in the second parameter you can set expiration date. It's number of seconds since **now** in Unix timestamp.
```php
signed_anchor('controller/method');
// https://example.com/controller/method?signature=TAxnWuXpqnKJ0C5A8nCedWYFvpw
```

```php
signed_anchor('controller/method', DAY);
// https://example.com/controller/method?expires=1672079033&signature=2XAKiFWYBPambJ7djstsHkUSJvk
```

#### signed_route_to()

This helper is similar to standard `route_to()`, except that in the second parameter you can set expiration date. It's number of seconds since **now** in Unix timestamp.
```php
signed_route_to('namedRoute');
// https://example.com/controller/method?signature=TAxnWuXpqnKJ0C5A8nCedWYFvpw
```

```php
signed_route_to('namedRoute', DAY);
// https://example.com/controller/method?expires=1672079033&signature=2XAKiFWYBPambJ7djstsHkUSJvk
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
