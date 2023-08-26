# CodeIgniter Signed URL

Prevent manual URL manipulation and auto expiry URLs.

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
// https://example.com/route/name/12?expiration=1671980371&signature=signature-goes-here
```

## Versions

Versions are not compatible - URLs generated in one version of Signed URL will not work with another version.

| CodeIgniter version | Signed URL version |
|---------------------|--------------------|
| `>= 4.4`            | `2.*`              |
| `< 4.4`             | `1.*`              |

## Docs

https://michalsn.github.io/codeigniter-signed-url
