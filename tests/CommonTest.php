<?php

namespace Tests;

use CodeIgniter\Config\Services;
use CodeIgniter\I18n\Time;
use CodeIgniter\Router\Exceptions\RouterException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @internal
 */
final class CommonTest extends CIUnitTestCase
{
    private App $config;

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);

        $this->config             = new App();
        $this->config->baseURL    = 'https://example.com/';
        config('Encryption')->key = hex2bin('6ece79d55cd04503600bd97520a0138a067690112fbfb44c704b0c626a7c62a2');
    }

    public function testSignedSiteUrl()
    {
        $this->assertSame(
            'https://example.com/index.php/controller/method?signature=ZFCzKztQmn2yGb-ShnNyT5mF4eQ',
            signed_site_url(['controller', 'method'], null, null, $this->config)
        );
    }

    public function testSignedSiteUrlWithExpirationTime()
    {
        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertSame(
            'https://example.com/index.php/controller/method?expires=1671980361&signature=byUOHLW6p45GrUpMsVz3AlEBMYs',
            signed_site_url('controller/method', SECOND * 10, null, $this->config)
        );
    }

    public function testSignedAnchor()
    {
        $this->assertSame(
            '<a href="https://example.com/index.php/controller/method?signature=ZFCzKztQmn2yGb-ShnNyT5mF4eQ" class="sample">https://example.com/index.php/controller/method?signature=ZFCzKztQmn2yGb-ShnNyT5mF4eQ</a>',
            signed_anchor(['controller', 'method'], null, '', 'class="sample"', $this->config)
        );
    }

    public function testSignedAnchorWithExpirationTime()
    {
        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $this->assertSame(
            '<a href="https://example.com/index.php/controller/method?expires=1671980361&signature=byUOHLW6p45GrUpMsVz3AlEBMYs" class="sample">title</a>',
            signed_anchor('controller/method', SECOND * 10, 'title', 'class="sample"', $this->config)
        );
    }

    public function testSignedUrlTo()
    {
        $routes = service('routes');
        $routes->add('path/(:num)', 'myController::goto/$1', ['as' => 'gotoPage']);

        $expected = 'https://example.com/index.php/path/13?signature=iZd5igbJp6uYIjjLKdiiPkmON0E';
        $this->assertSame($expected, signed_url_to('gotoPage', null, 13));
    }

    public function testSignedUrlToWithExpirationTime()
    {
        $routes = service('routes');
        $routes->add('path/(:num)', 'myController::goto/$1', ['as' => 'gotoPage']);

        Time::setTestNow('2022-12-25 14:59:11', 'UTC');

        $expected = 'https://example.com/index.php/path/13?expires=1671980361&signature=HTGY25XucRbwm9LffdsTWHzn1Eg';
        $this->assertSame($expected, signed_url_to('gotoPage', SECOND * 10, 13));
    }

    public function testSignedUrlToThrowControllerNotFound()
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Controller or its method is not found: Controller::method');
        signed_url_to('Controller::method', null, 13);
    }

    public function testSignedUrlToThrowInvalidRoute()
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('The route for "gotoPage" cannot be found.');

        signed_url_to('gotoPage', null, 13);
    }
}
