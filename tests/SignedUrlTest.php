<?php

namespace Tests;

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\I18n\Time;
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
    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);

        config('Encryption')->key = hex2bin('6ece79d55cd04503600bd97520a0138a067690112fbfb44c704b0c626a7c62a2');
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
        $this->expectExceptionMessage('Expiration, Signature or Algorithm keys cannot share the same name.');

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

        $expectedUrl .= '&signature=T3Y2OoBY2KvUbkTTBpPqjXFgs0k';

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

        $expectedUrl .= '&algorithm=sha1&signature=fBY7AIRdMqyhRwknzK3lPusRoWw';

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

        $expectedUrl .= '&expires=1671980361&signature=ILQnUh4hW3O9qEM541lZFgexlB4';

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

        $expectedUrl .= '&expires=1671980371&signature=GSU95yKkJm3DqU5t3ZyYxUpgmBI';

        $this->assertSame($expectedUrl, $url);
    }

    public function testVerifyWithIndexPage(): void
    {
        $path = '/index.php/path?query=string&signature=q0qKGOtgw3F153F1W3HZ0hUwxGc';
        $url  = 'https://example.com' . $path;

        $_SERVER['REQUEST_URI'] = $path;

        $uri       = new URI($url);
        $request   = new IncomingRequest(new App(), $uri, null, new UserAgent());
        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertTrue($signedUrl->verify($request));
    }

    public function testVerifyWithoutExpiration(): void
    {
        $path = '/path?query=string&signature=9IOk6sKK9VmpboZXQCFa-Xv2BEE';
        $url  = 'https://example.com' . $path;

        $_SERVER['REQUEST_URI'] = $path;

        $uri       = new URI($url);
        $request   = new IncomingRequest(new App(), $uri, null, new UserAgent());
        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertTrue($signedUrl->verify($request));
    }

    public function testVerifyWithExpiration(): void
    {
        $path = '/path?query=string&expires=1671980371&signature=VQ1Nu3FAYcKKO3FrdmjFLk6PxNQ';
        $url  = 'https://example.com' . $path;

        $_SERVER['REQUEST_URI'] = $path;

        $uri     = new URI($url);
        $request = new IncomingRequest(new App(), $uri, null, new UserAgent());

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertTrue($signedUrl->verify($request));
    }

    public function testVerifyThrowExceptionForMissingSignature(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('This URL have to be signed.');

        $path = '/path?query=string';
        $url  = 'https://example.com' . $path;

        $_SERVER['REQUEST_URI'] = $path;

        $uri     = new URI($url);
        $request = new IncomingRequest(new App(), $uri, null, new UserAgent());

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $signedUrl->verify($request);
    }

    public function testVerifyThrowExceptionForInvalidAlgorithm(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Algorithm is invalid or not supported.');

        $path = '/path?query=string&algorithm=fake&signature=fake';
        $url  = 'https://example.com' . $path;

        $_SERVER['REQUEST_URI'] = $path;

        $uri     = new URI($url);
        $request = new IncomingRequest(new App(), $uri, null, new UserAgent());

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $signedUrl->verify($request);
    }

    public function testVerifyThrowExceptionForUrlNotValid(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('URL is not valid.');

        $path = '/path?query=string123&expires=1671980371&signature=GSU95yKkJm3DqU5t3ZyYxUpgmBI';
        $url  = 'https://example.com' . $path;

        $_SERVER['REQUEST_URI'] = $path;

        $uri     = new URI($url);
        $request = new IncomingRequest(new App(), $uri, null, new UserAgent());

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $signedUrl->verify($request);
    }

    public function testVerifyThrowExceptionForExpiredUrl(): void
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('This URL has expired.');

        $path = '/path?query=string&expires=1671980371&signature=VQ1Nu3FAYcKKO3FrdmjFLk6PxNQ';
        $url  = 'https://example.com' . $path;

        $_SERVER['REQUEST_URI'] = $path;

        $uri     = new URI($url);
        $request = new IncomingRequest(new App(), $uri, null, new UserAgent());

        Time::setTestNow('2022-12-25 15:59:11', 'UTC');

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $signedUrl->verify($request);
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
