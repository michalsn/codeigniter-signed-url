<?php

namespace Tests;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURIFactory;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\I18n\Time;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Michalsn\CodeIgniterSignedUrl\Config\SignedUrl as SignedUrlConfig;
use Michalsn\CodeIgniterSignedUrl\Exceptions\SignedUrlException;
use Michalsn\CodeIgniterSignedUrl\SignedUrl;

/**
 * @internal
 */
final class SignedUrlTest extends CIUnitTestCase
{
    private App $config;

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);

        $this->config            = new App();
        $this->config->baseURL   = 'http://example.com/';
        $this->config->indexPage = '';

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '';

        config('Encryption')->key = hex2bin('6ece79d55cd04503600bd97520a0138a067690112fbfb44c704b0c626a7c62a2');
    }

    private function createRequest(?App $config = null, $body = null, ?string $path = null)
    {
        $config ??= new App();

        $factory = new SiteURIFactory($config, new Superglobals());
        $uri     = $factory->createFromGlobals();

        if ($path !== null) {
            $uri->setPath($path);
        }

        $request = new IncomingRequest($config, $uri, $body, new UserAgent());

        Factories::injectMock('config', 'App', $config);

        return $request;
    }

    public function testIncorrectAlgorithm(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Algorithm is incorrect, please run command: "php spark signedurl:algorithms" to see available options.');

        $config            = new SignedUrlConfig();
        $config->algorithm = '';
        new SignedUrl($config);
    }

    public function testMissingExpirationKey(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Expiration key cannot be empty.');

        $config                = new SignedUrlConfig();
        $config->expirationKey = '';
        new SignedUrl($config);
    }

    public function testMissingTokenKey(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Token key cannot be empty.');

        $config           = new SignedUrlConfig();
        $config->tokenKey = '';
        new SignedUrl($config);
    }

    public function testMissingSignatureKey(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Signature key cannot be empty.');

        $config               = new SignedUrlConfig();
        $config->signatureKey = '';
        new SignedUrl($config);
    }

    public function testMissingAlgorithmKey(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Algorithm key cannot be empty.');

        $config               = new SignedUrlConfig();
        $config->algorithmKey = '';
        new SignedUrl($config);
    }

    public function testSameKeyNames(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Expiration, Token, Signature or Algorithm keys cannot share the same name.');

        $config                = new SignedUrlConfig();
        $config->expirationKey = 'same';
        $config->signatureKey  = 'same';
        new SignedUrl($config);
    }

    public function testMissingEncryptionKey(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Encryption key is missing, please run command: "php spark key:generate"');

        config('Encryption')->key = '';

        $config = new SignedUrlConfig();
        new SignedUrl($config);
    }

    public function testSignWithNoExpirationInConfig(): void
    {
        $expectedUrl = 'https://example.com/path?query=string';
        $uri         = new URI($expectedUrl);

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $url       = $signedUrl->sign($uri);

        $expectedUrl .= '&signature=ongZW4ttfJMqN757mwNXp5kx_3snwQhaDyI6JiV-5FM';

        $this->assertSame($expectedUrl, $url);
    }

    public function testSignWithIncludedAlgorithm(): void
    {
        $expectedUrl = 'https://example.com/path?query=string';
        $uri         = new URI($expectedUrl);

        $config                      = new SignedUrlConfig();
        $config->includeAlgorithmKey = true;

        $signedUrl = new SignedUrl($config);
        $url       = $signedUrl->sign($uri);

        $expectedUrl .= '&algorithm=sha256&signature=IldvSUQVJqTc8Gq47i0pEvuUYNjK_oRX1PAw-ZaXyM4';

        $this->assertSame($expectedUrl, $url);
    }

    public function testSignWithExpirationFromConfig(): void
    {
        $expectedUrl = 'https://example.com/path?query=string';
        $uri         = new URI($expectedUrl);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $config             = new SignedUrlConfig();
        $config->expiration = SECOND * 10;
        $signedUrl          = new SignedUrl($config);
        $url                = $signedUrl->sign($uri);

        $expectedUrl .= '&expires=1671980361&signature=qohLh7fvypmDF9vktdJ6DBXH6fiKyBezNQblosN2sbA';

        $this->assertSame($expectedUrl, $url);
    }

    public function testSignWithOverwritenExpirationFromConfig(): void
    {
        $expectedUrl = 'https://example.com/path?query=string';
        $uri         = new URI($expectedUrl);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $config             = new SignedUrlConfig();
        $config->expiration = SECOND * 10;
        $signedUrl          = new SignedUrl($config);
        $url                = $signedUrl->setExpiration(SECOND * 20)->sign($uri);

        $expectedUrl .= '&expires=1671980371&signature=IzHjHhkTOOBPTayZnk8f_ut0H4-3q0YrDb11slKPWWE';

        $this->assertSame($expectedUrl, $url);
    }

    public function testVerifyWithIndexPage(): void
    {
        $this->config->indexPage = 'index.php';
        $_SERVER['SCRIPT_NAME']  = '/index.php';

        $_SERVER['REQUEST_URI'] = '/path?query=string&signature=joVnKjlHYIeuLtyUW5SnQ-US2FPkWkykZnSmf2D_RZY';

        $request = $this->createRequest($this->config);

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertTrue($signedUrl->verify($request));
    }

    public function testVerifyWithoutExpiration(): void
    {
        $_SERVER['REQUEST_URI'] = '/path?query=string&signature=iBEmAoQ9cPafZ3N05b9jEMj906Nd5nmSsJV7rKzFZSY';

        $request = $this->createRequest($this->config);

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertTrue($signedUrl->verify($request));
    }

    public function testVerifyWithExpiration(): void
    {
        $_SERVER['REQUEST_URI'] = '/path?query=string&expires=1671980371&signature=9GNwvgcsK7jJUPpXe3MK5xFbE0rb5ZBHIjKc1qqWSgU';

        $request = $this->createRequest($this->config);

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertTrue($signedUrl->verify($request));
    }

    public function testVerifyThrowExceptionForMissingSignature(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('This URL have to be signed.');

        $_SERVER['REQUEST_URI'] = '/path?query=string';

        $request = $this->createRequest($this->config);

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $signedUrl->verify($request);
    }

    public function testVerifyThrowExceptionForInvalidAlgorithm(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Algorithm is invalid or not supported.');

        $_SERVER['REQUEST_URI'] = '/path?query=string&algorithm=fake&signature=fake';

        $request = $this->createRequest($this->config);

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $signedUrl->verify($request);
    }

    public function testVerifyThrowExceptionForUrlNotValid(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('URL is not valid.');

        $_SERVER['REQUEST_URI'] = '/path?query=string123&expires=1671980371&signature=GSU95yKkJm3DqU5t3ZyYxUpgmBI';

        $request = $this->createRequest($this->config);

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $signedUrl->verify($request);
    }

    public function testVerifyThrowExceptionForExpiredUrl(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('This URL has expired.');

        $_SERVER['REQUEST_URI'] = '/path?query=string&expires=1671980371&signature=9GNwvgcsK7jJUPpXe3MK5xFbE0rb5ZBHIjKc1qqWSgU';

        $request = $this->createRequest($this->config);

        Time::setTestNow('2022-12-25 15:59:11', 'UTC');

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $signedUrl->verify($request);
    }

    public function testShouldRedirectTo(): void
    {
        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $this->assertNull($signedUrl->shouldRedirectTo());
    }

    public function testShouldRedirect(): void
    {
        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $this->assertFalse($signedUrl->shouldRedirect());
    }

    public function testShouldShow404(): void
    {
        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $this->assertFalse($signedUrl->shouldShow404());
    }
}
