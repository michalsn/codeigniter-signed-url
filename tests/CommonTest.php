<?php

namespace Tests;

use CodeIgniter\Config\Services;
use CodeIgniter\I18n\Time;
use CodeIgniter\Router\Exceptions\RouterException;
use CodeIgniter\Test\CIUnitTestCase;
use Michalsn\CodeIgniterSignedUrl\SignedUrl;

/**
 * @internal
 */
final class CommonTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);

        config('Encryption')->key = hex2bin('6ece79d55cd04503600bd97520a0138a067690112fbfb44c704b0c626a7c62a2');
    }

    public function testSignedurl(): void
    {
        $this->assertInstanceOf(SignedUrl::class, signedurl());
    }

    public function testSignedUrlSiteUrl(): void
    {
        $this->assertSame(
            'https://example.com/index.php/controller/method?signature=I0a1XPGLCTlRQo5c5f3LCz9R-tKP244-6pKCRV54AEk',
            signedurl()->siteUrl(['controller', 'method'])
        );
    }

    public function testSignedUrlSiteUrlWithExpirationTime(): void
    {
        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertSame(
            'https://example.com/index.php/controller/method?expires=1671980361&signature=9ZKau6qjzGOPY6unRPozK7dtZB1k_5hHQ9j3pwaQmzU',
            signedurl()->setExpiration(SECOND * 10)->siteUrl('controller/method')
        );
    }

    public function testSignedUrlTo(): void
    {
        $routes = service('routes');
        $routes->add('path/(:num)', 'myController::goto/$1', ['as' => 'gotoPage']);

        $this->assertSame(
            'https://example.com/index.php/path/13?signature=niwm-RgYXkGSKzuEH1semjC6TU5T8WrHs7FvEEyD8uQ',
            signedurl()->urlTo('gotoPage', 13)
        );
    }

    public function testSignedUrlToWithExpirationTime(): void
    {
        $routes = service('routes');
        $routes->add('path/(:num)', 'myController::goto/$1', ['as' => 'gotoPage']);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertSame(
            'https://example.com/index.php/path/13?expires=1671980361&signature=pHMHFrXI74G5JuQc1mUUETznuUNnpHkwhAOsjazxlUw',
            signedurl()->setExpiration(SECOND * 10)->urlTo('gotoPage', 13)
        );
    }

    public function testSignedUrlToThrowControllerNotFound(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Controller or its method is not found: Controller::method');
        signedurl()->urlTo('Controller::method', 13);
    }

    public function testSignedUrlToThrowInvalidRoute(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('The route for "gotoPage" cannot be found.');

        signedurl()->urlTo('gotoPage', 13);
    }
}
