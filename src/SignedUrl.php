<?php

namespace Michalsn\CodeIgniterSignedUrl;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;
use CodeIgniter\Router\Exceptions\RouterException;
use Config\App;
use Michalsn\CodeIgniterSignedUrl\Config\SignedUrl as SignedUrlConfig;
use Michalsn\CodeIgniterSignedUrl\Exceptions\SignedUrlException;

class SignedUrl
{
    protected ?string $key;
    protected ?int $tempExpiration;

    public function __construct(protected SignedUrlConfig $config)
    {
        $this->key = config('Encryption')->key;

        if (empty($this->config->algorithm) || ! in_array($this->config->algorithm, hash_hmac_algos(), true)) {
            throw SignedUrlException::forIncorrectAlgorithm();
        }

        if (empty($this->config->expirationKey)) {
            throw SignedUrlException::forEmptyExpirationKey();
        }

        if (empty($this->config->tokenKey)) {
            throw SignedUrlException::forEmptyTokenKey();
        }

        if (empty($this->config->signatureKey)) {
            throw SignedUrlException::forEmptySignatureKey();
        }

        if (empty($this->config->algorithmKey)) {
            throw SignedUrlException::forEmptyAlgorithmKey();
        }

        if (count(array_unique([$this->config->expirationKey, $this->config->tokenKey, $this->config->signatureKey, $this->config->algorithmKey])) !== 4) {
            throw SignedUrlException::forSameKeyNames();
        }

        if (empty($this->key)) {
            throw SignedUrlException::forEmptyEncryptionKey();
        }

        $this->resetSettings();
    }

    /**
     * Reset settings between calls.
     */
    protected function resetSettings(): void
    {
        $this->tempExpiration = $this->config->expiration;
    }

    /**
     * Set the URL expiration time.
     */
    public function setExpiration(?int $sec): static
    {
        $this->tempExpiration = $sec;

        return $this;
    }

    /**
     * Similar to site_url() helper function but with ability of sign the URL.
     */
    public function siteUrl(array|string $relativePath): string
    {
        if (is_array($relativePath)) {
            $relativePath = implode('/', $relativePath);
        }

        $host = service('request')->getUri()->getHost();

        $config = config(App::class);

        $uri = new SiteURI($config, $relativePath, $host);

        return $this->sign($uri);
    }

    /**
     * Similar to url_to() helper function but with ability of sign the URL.
     *
     * @throws RouterException
     */
    public function urlTo(string $controller, int|string ...$args): string
    {
        if (! $route = route_to($controller, ...$args)) {
            $explode = explode('::', $controller);

            if (isset($explode[1])) {
                throw RouterException::forControllerNotFound($explode[0], $explode[1]);
            }

            throw RouterException::forInvalidRoute($controller);
        }

        return $this->siteUrl($route);
    }

    /**
     * Transform the URI to signed URL.
     */
    public function sign(URI $uri): string
    {
        if ($this->tempExpiration !== null) {
            $uri->addQuery($this->config->expirationKey, Time::now()->addSeconds($this->tempExpiration)->getTimestamp());
        }

        if ($this->config->token !== null) {
            helper('text');
            $uri->addQuery($this->config->tokenKey, random_string('alnum', $this->config->token));
        }

        if ($this->config->includeAlgorithmKey) {
            $uri->addQuery($this->config->algorithmKey, $this->config->algorithm);
        }

        $url       = URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath(), $uri->getQuery(), $uri->getFragment());
        $signature = base64url_encode(hash_hmac($this->config->algorithm, $url, $this->key, true));

        $uri->addQuery($this->config->signatureKey, $signature);

        $url = URI::createURIString($uri->getScheme(), $uri->getAuthority(), $uri->getPath(), $uri->getQuery(), $uri->getFragment());

        $this->resetSettings();

        return $url;
    }

    /**
     * Verify if URL is properly signed
     *
     * @throws SignedUrlException
     */
    public function verify(IncomingRequest $request): bool
    {
        $querySignature  = $request->getGet($this->config->signatureKey);
        $queryExpiration = (int) $request->getGet($this->config->expirationKey);
        $queryAlgorithm  = $request->getGet($this->config->algorithmKey) ?? $this->config->algorithm;

        if (empty($querySignature)) {
            throw SignedUrlException::forMissingSignature();
        }

        if (empty($queryAlgorithm) || ! in_array($queryAlgorithm, hash_hmac_algos(), true)) {
            throw SignedUrlException::forInvalidAlgorithm();
        }

        $querySignature = base64url_decode($querySignature);

        $uri = $request->getUri();
        $uri->stripQuery($this->config->signatureKey);

        $url       = URI::createURIString('', base_url(), $uri->getPath(), $uri->getQuery(), $uri->getFragment());
        $signature = hash_hmac($queryAlgorithm, $url, $this->key, true);

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
