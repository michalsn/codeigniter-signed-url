<?php

if (! function_exists('signedurl')) {
    /**
     * Returns SignedUrl instance.
     */
    function signedurl()
    {
        return service('signedurl');
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
