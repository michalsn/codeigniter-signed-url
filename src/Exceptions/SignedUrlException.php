<?php

namespace Michalsn\CodeIgniterSignedUrl\Exceptions;

use RuntimeException;

final class SignedUrlException extends RuntimeException
{
    public static function forIncorrectAlgorithm(): static
    {
        return new self(lang('SignedUrl.incorrectAlgorithm'));
    }

    public static function forEmptyExpirationKey(): static
    {
        return new self(lang('SignedUrl.emptyExpirationKey'));
    }

    public static function forEmptySignatureKey(): static
    {
        return new self(lang('SignedUrl.emptySignatureKey'));
    }

    public static function forEmptyAlgorithmKey(): static
    {
        return new self(lang('SignedUrl.emptyAlgorithmKey'));
    }

    public static function forSameKeyNames(): static
    {
        return new self(lang('SignedUrl.sameKeyNames'));
    }

    public static function forEmptyEncryptionKey(): static
    {
        return new self(lang('SignedUrl.emptyEncryptionKey'));
    }

    public static function forMissingSignature(): static
    {
        return new self(lang('SignedUrl.missingSignature'));
    }

    public static function forUrlNotValid(): static
    {
        return new self(lang('SignedUrl.urlNotValid'));
    }

    public static function forUrlExpired(): static
    {
        return new self(lang('SignedUrl.urlExpired'));
    }
}
