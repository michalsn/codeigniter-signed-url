# Filters

## Overview

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
}
```

## Options

By default, this filter will throw `SignedUrlException` when the URL won't be signed or will be expired. But there are other options, and we can enable them by editing the config file:

* We can redirect to the previous page
* Or show 404 page

More info you can find in the Config section.

!!! note

    Remember, that if the filter implementation doesn't suit you, you can always create your own, which will behave differently upon an error. You can also not use the filter at all and make the check in the controller.
