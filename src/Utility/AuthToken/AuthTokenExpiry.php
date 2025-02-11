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
 * @since         3.0.0
 */
namespace App\Utility\AuthToken;

use App\Model\Table\AuthenticationTokensTable;
use Cake\Core\Configure;
use Cake\Http\Exception\InternalErrorException;
use InvalidArgumentException;

class AuthTokenExpiry
{
    /**
     * @param string $tokenType Token type
     * @return string
     */
    public function getExpiryForTokenType(string $tokenType): string
    {
        if (!in_array($tokenType, AuthenticationTokensTable::ALLOWED_TYPES)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid $tokenType `%s`. Must be one of `%s`.',
                    $tokenType,
                    implode(',', AuthenticationTokensTable::ALLOWED_TYPES)
                )
            );
        }

        $tokenTypeExpiry = Configure::read(sprintf('cipherguard.auth.token.%s.expiry', $tokenType));

        if (!is_string($tokenTypeExpiry)) {
            $tokenTypeExpiry = Configure::read('cipherguard.auth.tokenExpiry');
        }

        if (!is_string($tokenTypeExpiry)) {
            $msg = 'No default expiry or expiry for token type ' . $tokenTypeExpiry;
            throw new InternalErrorException($msg);
        }

        return $tokenTypeExpiry;
    }
}
