# Filters

## Overview

To validate signed URLs we can use build in filter. We can enable it in one simple step.

Define when filter should be fired up. In the example below we will assume it will be used when the first segment of the url will contain `signed-urls` string.

```php
// app/Config/Filters.php
<?php

// ...

class Filters extends BaseConfig
{

    // ...

    public $filters = [
        'signedurl' => ['before' => ['signed-urls/*']],
    ];
}
```

## Options

By default, this filter will throw `SignedUrlException` when the URL won't be signed or will be expired. But there are other options, and we can enable them by editing the config file:

* We can redirect to the previous page
* Or show 404 page

More info you can find in the [Configuration](configuration.md) page.

!!! note

    Remember, that if the filter implementation doesn't suit you, you can always [create your own](https://codeigniter.com/user_guide/incoming/filters.html?highlight=filter#creating-a-filter), which will behave differently upon an error. You can also not use the filter at all and make the check in the controller.
