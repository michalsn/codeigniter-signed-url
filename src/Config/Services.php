<?php

namespace Michalsn\CodeIgniterSignedUrl\Config;

use CodeIgniter\Config\BaseService;
use Michalsn\CodeIgniterSignedUrl\Config\SignedUrl as SignedUrlConfig;
use Michalsn\CodeIgniterSignedUrl\SignedUrl;

class Services extends BaseService
{
    /**
     * Return the signed url class.
     *
     * @return SignedUrl
     */
    public static function signedurl(?SignedUrlConfig $config = null, bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('signedurl', $config);
        }

        $config ??= config('SignedUrl');

        return new SignedUrl($config);
    }
}
