<?php

namespace Michalsn\CodeIgniterSignedUrl\Config;

use CodeIgniter\Config\BaseConfig;

class SignedUrl extends BaseConfig
{
    /**
     * Number of seconds in unix timestamp
     * will be added to the current date.
     */
    public ?int $expiration = null;

    /**
     * Length of the token string used with
     * random_string() helper. Useful if you have
     * very few changing parameters in the URL.
     */
    public ?int $token = null;

    /**
     * Algorithm used to sign the URL.
     *
     * For available options, please run:
     *     php spark signedurl:algorithms
     *
     * If you're not sure what you're doing
     * please stay with the default option.
     */
    public string $algorithm = 'sha256';

    /**
     * Query string key names.
     */
    public string $expirationKey = 'expires';

    public string $tokenKey     = 'token';
    public string $signatureKey = 'signature';
    public string $algorithmKey = 'algorithm';

    /**
     * Include algorithmKey to the query string.
     */
    public bool $includeAlgorithmKey = false;

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
