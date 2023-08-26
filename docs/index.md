# CodeIgniter Signed URL Documentation

This library makes it easy to sign URLs in CodeIgniter 4 framework. It can be used to **prevent manual URL manipulation** or to **auto expiry links** that have been given to the end user.

## Overview

We can sign URLs very easy with two main methods that act similar to the helper functions known from CodeIgniter's URL helper.

```php
echo signedurl()->siteUrl('controller/method?query=string');
// https://example.com/controller/method?query=string&signature=signature-goes-here
```

```php
echo signedurl()->setExpiration(DAY * 2)->urlTo('namedRoute', 12);
// https://example.com/route/name/12?expiration=1671980371&signature=signature-goes-here
```

## Versions

Versions are not compatible - URLs generated in one version of Signed URL will not work with another version.

| CodeIgniter version | Signed URL version |
|---------------------|--------------------|
| `>= 4.4`            | `2.*`              |
| `< 4.4`             | `1.*`              |
