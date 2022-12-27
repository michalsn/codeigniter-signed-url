# Configuration

To make changes to the config file, we have to have our copy in the `app/Config/SignedUrl.php`. Luckily, this package comes with handy command that will make this easy.

When we run:

    php spark signedurl:publish

We will get our copy ready for modifications.

---

Available options:

- [$expiration](#expiration)
- [$algorithm](#algorithm)
- [$expirationKey](#expirationKey)
- [$signatureKey](#signatureKey)
- [$algorithmKey](#algorithmKey)
- [$includeAlgorithmKey](#includeAlgorithmKey)
- [$redirect](#redirect)
- [$show404](#show404)

### $expiration

This setting allows us to set a fixed time after which the signed URL will expire.
It's number of seconds in unix timestamp that will be added to the current date.

By default, this is set to `null`.

### $algorithm

This setting allows us to set algorithm that will be used during signing the URLs.

By default, this is set to `sha1`.

!!! note

    If you're not sure what you're doing please stay with the default option.

You can see the list of all available options when running command:

    php spark signedurl:algorithms

!!! warning

    When you don't include used algorithm to the query string (default), then changing algorithm will result with invalidating all the generated URLs.

### $expirationKey

This is the name of the query string key, which will be responsible for storing the time after which the URL will expire.

By default, this is set to `expires`.

!!! note

    Whatever name you will choose, treat it as a restricted name and don't use it as a part of the query string in your code.

### $signatureKey

This is the name of the query string key, which will be responsible for storing the signature by which the validity of the entire URL will be checked.

By default, this is set to `signature`.

!!! note

    Whatever name you will choose, treat it as a restricted name and don't use it as a part of the query string in your code.

### $algorithmKey

This is the name of the query string key, which will be responsible for storing the algorithm by which the validity of the entire URL will be checked.

By default, this is set to `algorithm`.

!!! note

    Whatever name you will choose, treat it as a restricted name and don't use it as a part of the query string in your code.

### $includeAlgorithmKey

This setting determines if the algorithm will be included to the query string of the generated URL.

By default, this is set to `false`.

### $redirect

This setting is used in the Filter to determine whether we will redirect user to the previous page with the `error`, when URL will not be valid or expired.

By default, this is set to `false`.

### $show404

This setting is used in the Filter to determine whether we will show a 404 page, when URL will not be valid or expired.

By default, this is set to `false`.
