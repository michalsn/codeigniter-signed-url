<?php

namespace Michalsn\CodeIgniterSignedUrl\Config;

use Michalsn\CodeIgniterSignedUrl\Filters\SignedUrl;

class Registrar
{
    /**
     * Register the CodeIgniterSignedUrl filter.
     */
    public static function Filters(): array
    {
        return [
            'aliases' => [
                'signedurl' => SignedUrl::class,
            ],
        ];
    }
}
