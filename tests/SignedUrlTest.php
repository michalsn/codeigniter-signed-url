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

    public function testMissingExpirationKey()
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Expiration key cannot be empty.');

        $config                = new SignedUrlConfig();
        $config->expirationKey = '';
        new SignedUrl($config);
    }

    public function testMissingSignatureKey()
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Signature key cannot be empty.');

        $config               = new SignedUrlConfig();
        $config->signatureKey = '';
        new SignedUrl($config);
    }

    public function testSameExpirationAndSignatureKey()
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Expiration, Signature or Algorithm keys cannot share the same name.');

        $config                = new SignedUrlConfig();
        $config->expirationKey = 'same';
        $config->signatureKey  = 'same';
        new SignedUrl($config);
    }

    public function testMissingEncryptionKey()
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('Encryption key is missing, please run command: "php spark key:generate"');

        config('Encryption')->key = '';

        $config = new SignedUrlConfig();
        new SignedUrl($config);
    }

    public function testSignWithNoExpirationInConfig()
    {
        $expectedUrl = 'https://example.com/path?query=string';
        $uri         = new URI($expectedUrl);

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $url       = $signedUrl->sign($uri);

        $expectedUrl .= '&signature=T3Y2OoBY2KvUbkTTBpPqjXFgs0k';

        $this->assertSame($expectedUrl, $url);
    }

    public function testSignWithExpirationFromConfig()
    {
        $expectedUrl = 'https://example.com/path?query=string';
        $uri         = new URI($expectedUrl);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $config                 = new SignedUrlConfig();
        $config->expirationTime = SECOND * 10;
        $signedUrl              = new SignedUrl($config);
        $url                    = $signedUrl->sign($uri);

        $expectedUrl .= '&expires=1671980361&signature=ILQnUh4hW3O9qEM541lZFgexlB4';

        $this->assertSame($expectedUrl, $url);
    }

    public function testSignWithOverwritenExpirationFromConfig()
    {
        $expectedUrl = 'https://example.com/path?query=string';
        $uri         = new URI($expectedUrl);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $config                 = new SignedUrlConfig();
        $config->expirationTime = SECOND * 10;
        $signedUrl              = new SignedUrl($config);
        $url                    = $signedUrl->sign($uri, SECOND * 20);

        $expectedUrl .= '&expires=1671980371&signature=GSU95yKkJm3DqU5t3ZyYxUpgmBI';

        $this->assertSame($expectedUrl, $url);
    }

    public function testVerifyWithoutExpiration()
    {
        $path = '/path?query=string&signature=T3Y2OoBY2KvUbkTTBpPqjXFgs0k';
        $url  = 'https://example.com' . $path;

        $_SERVER['REQUEST_URI'] = $path;

        $uri       = new URI($url);
        $request   = new IncomingRequest(new App(), $uri, null, new UserAgent());
        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertTrue($signedUrl->verify($request));
    }

    public function testVerifyWithExpiration()
    {
        $path = '/path?query=string&expires=1671980371&signature=GSU95yKkJm3DqU5t3ZyYxUpgmBI';
        $url  = 'https://example.com' . $path;

        $_SERVER['REQUEST_URI'] = $path;

        $uri     = new URI($url);
        $request = new IncomingRequest(new App(), $uri, null, new UserAgent());

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertTrue($signedUrl->verify($request));
    }

    public function testVerifyThrowExceptionForMissingSignature()
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

    public function testVerifyThrowExceptionForUrlNotValid()
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

    public function testVerifyThrowExceptionForExpiredUrl()
    {
        $this->expectException(SignedUrlException::class);
        $this->expectExceptionMessage('This URL has expired.');

        $path = '/path?query=string&expires=1671980371&signature=GSU95yKkJm3DqU5t3ZyYxUpgmBI';
        $url  = 'https://example.com' . $path;

        $_SERVER['REQUEST_URI'] = $path;

        $uri     = new URI($url);
        $request = new IncomingRequest(new App(), $uri, null, new UserAgent());

        Time::setTestNow('2022-12-25 15:59:11', 'UTC');

        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $signedUrl->verify($request);
    }

    public function testShouldRedirect()
    {
        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $this->assertFalse($signedUrl->shouldRedirect());
    }

    public function testShouldShow404()
    {
        $config    = new SignedUrlConfig();
        $signedUrl = new SignedUrl($config);
        $this->assertFalse($signedUrl->shouldShow404());
    }
}
