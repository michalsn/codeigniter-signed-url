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

        if (empty($this->config->algorithm) || ! in_array($this->config->algorithm, hash_hmac_algos())) {
            throw SignedUrlException::forIncorrectAlgorithm();
        }

        if (empty($this->config->expirationKey)) {
            throw SignedUrlException::forEmptyExpirationKey();
        }

        if (empty($this->config->signatureKey)) {
            throw SignedUrlException::forEmptySignatureKey();
        }

        if (empty($this->config->algorithmKey)) {
            throw SignedUrlException::forEmptyAlgorithmKey();
        }

        if (count(array_unique([$this->config->expirationKey, $this->config->signatureKey, $this->config->algorithmKey])) !== 3) {
            throw SignedUrlException::forSameKeyNames();
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

        if ($this->config->includeAlgorithmKey) {
            $uri->addQuery($this->config->algorithmKey, $this->config->algorithm);
        }

        $url       = URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath(), $uri->getQuery(), $uri->getFragment());
        $signature = base64url_encode(hash_hmac($this->config->algorithm, $url, $this->key, true));

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

        $querySignature = base64url_decode($querySignature);

        $uri = $request->getUri();
        $uri->stripQuery($this->config->signatureKey);

        $url       = URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath(), $uri->getQuery(), $uri->getFragment());
        $signature = hash_hmac($this->config->algorithm, $url, $this->key, true);

        if (! hash_equals($querySignature, $signature)) {
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
