<?php

namespace Michalsn\CodeIgniterSignedUrl\Config;

use CodeIgniter\Config\BaseConfig;

class SignedUrl extends BaseConfig
{
    /**
     * Number of seconds in unix timestamp
     * will be added to the current date.
     */
    public ?int $expirationTime = null;

    /**
     * Query string key names.
     */
    public string $expirationKey = 'expires';

    public string $signatureKey = 'signature';

    /**
     * In Filter - redirect to the previous page
     * with error on failure.
     */
    public bool $redirect = false;

    /**
     * In Filter - show the 404 page with error
     * on failure.
     */
    public bool $show404 = false;
}
