<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.com Cipherguard(tm)
 * @since         4.1.0
 */

namespace App\Test\TestCase\Middleware;

use App\Middleware\SslForceMiddleware;
use App\Test\Lib\Http\TestRequestHandler;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @covers \App\Middleware\SslForceMiddleware
 */
class SslForceMiddlewareTest extends TestCase
{
    public function testSslForceMiddleware_HTTP_With_SSL_Force_should_redirect_to_https()
    {
        Configure::write(SslForceMiddleware::CIPHERGUARD_SSL_FORCE_CONFIG_NAME, true);
        $request = new ServerRequest();
        $uri = new Uri('http://cipherguard.test');

        $request = $request->withUri($uri);
        $middleware = new SslForceMiddleware();
        $response = $middleware->process($request, new TestRequestHandler());

        $this->assertSame(['https://cipherguard.test'], $response->getHeader('Location'));
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testSslForceMiddleware_HTTP_Without_SSL_Force_should_not_redirect_to_https()
    {
        Configure::write(SslForceMiddleware::CIPHERGUARD_SSL_FORCE_CONFIG_NAME, false);
        $request = new ServerRequest();
        $uri = new Uri('http://cipherguard.test');

        $request = $request->withUri($uri);
        $middleware = new SslForceMiddleware();
        $response = $middleware->process($request, new TestRequestHandler());

        $this->assertFalse($response->hasHeader('Location'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testSslForceMiddleware_HTTPS_With_SSL_Force_should_add_strict_transport_security()
    {
        Configure::write(SslForceMiddleware::CIPHERGUARD_SSL_FORCE_CONFIG_NAME, true);
        $request = new ServerRequest();
        $uri = new Uri('https://cipherguard.test');

        $request = $request->withUri($uri);
        $middleware = new SslForceMiddleware();
        $response = $middleware->process($request, new TestRequestHandler());

        $this->assertSame(['max-age=31536000; includeSubDomains'], $response->getHeader('strict-transport-security'));
    }
}
