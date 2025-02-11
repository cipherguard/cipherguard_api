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
 * @since         3.8.0
 */
namespace App\Test\Lib\Utility;

trait CookieTestTrait
{
    /**
     * @param mixed $expected Expected value
     * @param string $name Cookie name
     */
    public function assertCookieIsSecure($expected, string $name): void
    {
        $this->assertCookie($expected, $name);
        /** @var Response $response */
        $response = $this->_response;
        $cookie = $response->getCookieCollection()->get($name);
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
    }
}
