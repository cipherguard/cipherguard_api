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
 * @since         4.2.0
 */

namespace App\Test\TestCase\Middleware;

use App\Middleware\UuidParserMiddleware;
use App\Test\Lib\Http\TestRequestHandler;
use App\Utility\UuidFactory;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/**
 * @covers \App\Middleware\UuidParserMiddleware
 */
class UuidParserMiddlewareTest extends TestCase
{
    public function testUuidParserMiddleware_LowerUuids()
    {
        $uuid = UuidFactory::uuid();
        $UUID = strtoupper($uuid);
        $request = (new ServerRequest())
            ->withParam('pass', [$UUID, 'foo'])
            ->withParam('query', [$UUID, 'bar']);

        $middleware = new UuidParserMiddleware();
        $middleware->process($request, new TestRequestHandler());

        $request = $middleware->getRequest();
        $this->assertSame($uuid, $request->getParam('pass')[0]);
        $this->assertSame('foo', $request->getParam('pass')[1]);
        $this->assertSame($uuid, $request->getParam('query')[0]);
        $this->assertSame('bar', $request->getParam('query')[1]);
    }

    public function testUuidParserMiddleware_Nested_Data_Is_Ignored()
    {
        $uuid = UuidFactory::uuid();
        $UUID = strtoupper($uuid);
        $request = (new ServerRequest())
            ->withParam('query', [
                'query1' => $UUID,
                'filter' => [
                    'has-parent' => "$UUID",
                    'has-groups' => "$UUID,$UUID",
                ],
            ]);

        $middleware = new UuidParserMiddleware();
        $middleware->process($request, new TestRequestHandler());

        $request = $middleware->getRequest();
        $expectedQuery = [
            'query1' => $uuid,
            'filter' => [
                'has-parent' => "$UUID",
                'has-groups' => "$UUID,$UUID",
            ],
        ];
        $this->assertSame($expectedQuery, $request->getParam('query'));
    }
}
