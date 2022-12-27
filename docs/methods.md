# Methods

Available options:

- [setExpiration()](#setExpiration)
- [siteUrl()](#siteUrl)
- [urlTo()](#urlTo)
- [sign()](#sign)
- [verify()](#verify)

### setExpiration()

With this method we can set temporary value for expiration. The value set here will be reset when the: `siteUrl()`, `urlTo()` or `sign()` methods are called.

This is number of seconds in unix timestamp that will be added to the current date.

```php
service('signedurl')->setExpiration(DAY)->siteUrl('url');
```

!!! note

    If you want the URLs to always be valid for a certain period of time, you can set time in the `$expiration` variable in the configuration file.

### siteUrl()

This method is similar to the standard `site_url`, but it produces signed URL.

```php
service('signedurl')->siteUrl('controller/method');
```

### urlTo()

This method is similar to the standard `url_to`, but it produces signed URL.

```php
service('signedurl')->urlTo('namedRoute', 'param');
```

### sign()

With this method we can sign URI. Usually you won't be using this method directly, since it is used by other methods.

```php
service('signedurl')->sign($uri);
```

### verify()

With this method we can verify if given URL is properly signed and not expired if expiration timestamp was set during URL creation.

```php
service('signedurl')->verify($request);
```

The URL verification may take place automatically via Filter class, but you can also make it happen in your Controller instead. The choice is up to you.
