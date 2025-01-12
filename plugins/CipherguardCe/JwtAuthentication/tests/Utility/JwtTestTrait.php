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
 * @since         3.3.0
 */
namespace Cipherguard\JwtAuthentication\Test\Utility;

use Cipherguard\JwtAuthentication\Service\AccessToken\JwksGetService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

trait JwtTestTrait
{
    public function assertAccessTokenIsValid(string $accessToken, string $userId)
    {
        $publicKey = file_get_contents((new JwksGetService())->getKeyPath());
        $res = JWT::decode($accessToken, new Key($publicKey, 'RS256'));
        $this->assertSame($userId, $res->sub);
    }
}
