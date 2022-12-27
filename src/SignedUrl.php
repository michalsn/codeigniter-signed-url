<?php

namespace Michalsn\CodeIgniterSignedUrl;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;
use Michalsn\CodeIgniterSignedUrl\Config\SignedUrl as SignedUrlConfig;
use Michalsn\CodeIgniterSignedUrl\Exceptions\SignedUrlException;

class SignedUrl
{
    protected ?string $key;

    public function __construct(protected SignedUrlConfig $config)
    {
        $this->key = config('Encryption')->key;

        if (empty($this->config->expirationKey)) {
            throw SignedUrlException::forEmptyExpirationKey();
        }

        if (empty($this->config->signatureKey)) {
            throw SignedUrlException::forEmptySignatureKey();
        }

        if ($this->config->expirationKey === $this->config->signatureKey) {
            throw SignedUrlException::forSameExpirationAndSignatureKey();
        }

        if (empty($this->key)) {
            throw SignedUrlException::forEmptyEncryptionKey();
        }
    }

    /**
     * Encode URL to signed one
     */
    public function sign(URI $uri, ?int $expirationTime = null): string
    {
        $expirationTime ??= $this->config->expirationTime;

        if ($expirationTime !== null) {
            $uri->addQuery($this->config->expirationKey, Time::now()->addSeconds($expirationTime)->getTimestamp());
        }

        $url       = URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath(), $uri->getQuery(), $uri->getFragment());
        $signature = base64url_encode(hash_hmac('sha1', $url, $this->key, true));

        $uri->addQuery($this->config->signatureKey, $signature);

        return URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath(), $uri->getQuery(), $uri->getFragment());
    }

    /**
     * Verify if URL is properly signed
     *
     * @throws SignedUrlException
     */
    public function verify(IncomingRequest $request): bool
    {
        $querySignature  = $request->getGet($this->config->signatureKey);
        $queryExpiration = $request->getGet($this->config->expirationKey);

        if (empty($querySignature)) {
            throw SignedUrlException::forMissingSignature();
        }

        $uri = $request->getUri();
        $uri->stripQuery($this->config->signatureKey);

        $url       = URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath(), $uri->getQuery(), $uri->getFragment());
        $signature = hash_hmac('sha1', $url, $this->key, true);

        if (base64url_decode($querySignature) !== $signature) {
            throw SignedUrlException::forUrlNotValid();
        }

        if (! empty($queryExpiration) && Time::now()->getTimestamp() > $queryExpiration) {
            throw SignedUrlException::forUrlExpired();
        }

        return true;
    }

    /**
     * Check if redirect option is enabled.
     */
    public function shouldRedirect(): bool
    {
        return $this->config->redirect;
    }

    /**
     * Check if show 404 option is enabled.
     */
    public function shouldShow404(): bool
    {
        return $this->config->show404;
    }
}
