# Installation

- [Composer Installation](#composer-installation)
- [Manual Installation](#manual-installation)
- [Generate encryption key](#generate-encryption-key)

## Composer Installation

The only thing you have to do is to run this command, and you're ready to go.

```console
composer require michalsn/codeigniter-signed-url
```

## Manual Installation

In the example below we will assume, that files from this project will be located in `app/ThirdParty/signed-url` directory.

Download this project and then enable it by editing the `app/Config/Autoload.php` file and adding the `Michalsn\CodeIgniterSignedUrl` namespace to the `$psr4` array. You also have to add `Common.php` to the `$files` array, like in the below example:

```php
<?php

...

public $psr4 = [
    APP_NAMESPACE => APPPATH, // For custom app namespace
    'Config'      => APPPATH . 'Config',
    'Michalsn\CodeIgniterSignedUrl' => APPPATH . 'ThirdParty/signed-url/src',
];

...

public $files = [
    APPPATH . 'ThirdParty/signed-url/src/Common.php',
];
```

## Generate encryption key

Make sure that you have generated the encryption key. If not, please run command:

```console
php spark generate:key
```

!!! warning

    Please remember that any change made to the `encryption key` after generating signed URLs will auto expire them.


