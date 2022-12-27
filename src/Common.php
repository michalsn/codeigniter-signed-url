<?php

use CodeIgniter\Router\Exceptions\RouterException;
use Config\App;

if (! function_exists('signed_site_url')) {
    /**
     * Returns a signed site URL as defined by the App config.
     *
     * @param array|string $relativePath   URI string or array of URI segments
     * @param int|null     $expirationTime Number of seconds that will be added to current time()
     * @param App|null     $config         Alternate configuration to use
     */
    function signed_site_url(array|string $relativePath = '', ?int $expirationTime = null, ?string $scheme = null, ?App $config = null): string
    {
        // Convert array of segments to a string
        if (is_array($relativePath)) {
            $relativePath = implode('/', $relativePath);
        }

        $uri = _get_uri($relativePath, $config);

        return service('signedurl')->sign($uri, $expirationTime);
    }
}

if (! function_exists('signed_anchor')) {
    /**
     * Anchor Link
     *
     * Creates an anchor based on the local URL.
     *
     * @param array|string $uri            URI string or array of URI segments
     * @param int|null     $expirationTime Number of seconds that will be added to current time()
     * @param string       $title          The link title
     * @param string       $attributes     Any attributes
     * @param App|null     $altConfig      Alternate configuration to use
     */
    function signed_anchor(array|string $uri = '', ?int $expirationTime = null, string $title = '', $attributes = '', ?App $altConfig = null): string
    {
        // use alternate config if provided, else default one
        $config = $altConfig ?? config(App::class);

        $siteUrl = is_array($uri) ? signed_site_url($uri, $expirationTime, null, $config) : (preg_match('#^(\w+:)?//#i', $uri) ? $uri : signed_site_url($uri, $expirationTime, null, $config));
        // eliminate trailing slash
        $siteUrl = rtrim($siteUrl, '/');

        if ($title === '') {
            $title = $siteUrl;
        }

        if ($attributes !== '') {
            $attributes = stringify_attributes($attributes);
        }

        return '<a href="' . $siteUrl . '"' . $attributes . '>' . $title . '</a>';
    }
}

if (! function_exists('signed_url_to')) {
    /**
     * Get the full, absolute URL to a controller method
     * (with additional arguments)
     *
     * NOTE: This requires the controller/method to
     * have a route defined in the routes Config file.
     *
     * @param string     $controller     Named route or Controller::method
     * @param int|null   $expirationTime Number of seconds that will be added to current time()
     * @param int|string ...$args        One or more parameters to be passed to the route
     */
    function signed_url_to(string $controller, ?int $expirationTime = null, int|string ...$args): string
    {
        if (! $route = route_to($controller, ...$args)) {
            $explode = explode('::', $controller);

            if (isset($explode[1])) {
                throw RouterException::forControllerNotFound($explode[0], $explode[1]);
            }

            throw RouterException::forInvalidRoute($controller);
        }

        return signed_site_url($route, $expirationTime);
    }
}

if (! function_exists('base64url_decode')) {
    /**
     * Decodes base64url (RFC4648 Section 5) string
     *
     * @see https://www.rfc-editor.org/rfc/rfc4648#page-7
     *
     * @param string $input String encoded with base64url
     *
     * @return string
     */
    function base64url_decode(string $input)
    {
        $input .= str_repeat('=', (4 - strlen($input) % 4) % 4);

        return base64_decode(strtr($input, '-_', '+/'), true);
    }
}

if (! function_exists('base64url_encode')) {
    /**
     * Encodes a string with base64url (RFC4648 Section 5)
     *
     * @see https://www.rfc-editor.org/rfc/rfc4648#page-7
     *
     * @param string $input String to be encoded
     *
     * @return string
     */
    function base64url_encode(string $input)
    {
        $output = strtr(base64_encode($input), '+/', '-_');

        return str_replace('=', '', $output);
    }
}
